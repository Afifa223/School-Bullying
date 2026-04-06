<?php
require_once "teacher_ui.php";
teacher_require_login();
require_once "db.php";
require_once "fpdf186/fpdf.php"; 

$case_id = trim($_GET["case_id"] ?? "");

if ($case_id === "") {
  exit("Missing case ID.");
}

$stmt = $conn->prepare("
  SELECT 
    br.report_id,
    br.case_id,
    br.incident_type,
    br.severity,
    br.occurrence_datetime,
    br.location,
    br.description,
    br.status,
    br.submitted_at,
    br.teacher_note,
    s.first_name,
    s.last_name,
    s.email,
    s.roll_number,
    s.admission_year,
    t.full_name AS teacher_name
  FROM bullying_reports br
  LEFT JOIN student s ON s.student_id = br.owner_student_id
  LEFT JOIN teachers t ON t.teacher_id = br.assigned_teacher_id
  WHERE br.case_id = ?
  LIMIT 1
");
$stmt->bind_param("s", $case_id);
$stmt->execute();
$case = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$case) {
  exit("Case not found.");
}

if (($case["status"] ?? "") !== "resolved") {
  exit("PDF can only be generated when the case is resolved.");
}

$stmt = $conn->prepare("
  SELECT 
    h.old_status,
    h.new_status,
    h.teacher_note,
    h.changed_at,
    t.full_name AS teacher_name
  FROM case_status_history h
  LEFT JOIN teachers t ON t.teacher_id = h.teacher_id
  WHERE h.case_id = ?
  ORDER BY h.changed_at ASC, h.history_id ASC
");
$stmt->bind_param("s", $case_id);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("
  SELECT original_name, file_name, uploaded_at
  FROM report_evidence
  WHERE report_id = ?
  ORDER BY evidence_id ASC
");
$stmt->bind_param("i", $case["report_id"]);
$stmt->execute();
$evidence = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$followups = [];
$followup_table_exists = false;
$checkTable = $conn->query("SHOW TABLES LIKE 'followups'");
if ($checkTable && $checkTable->num_rows > 0) {
  $followup_table_exists = true;
}

if ($followup_table_exists) {
  $stmt = $conn->prepare("
    SELECT 
      f.followup_datetime,
      f.note,
      t.full_name AS teacher_name
    FROM followups f
    LEFT JOIN teachers t ON t.teacher_id = f.teacher_id
    WHERE f.report_id = ?
    ORDER BY f.followup_datetime ASC
  ");
  $stmt->bind_param("i", $case["report_id"]);
  $stmt->execute();
  $followups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

function status_label(string $status): string {
  return match($status) {
    "submitted"    => "Submitted",
    "seen"         => "Seen by Teacher",
    "under_review" => "Under Review",
    "action_taken" => "Action Taken",
    "resolved"     => "Resolved",
    default        => ucfirst(str_replace("_", " ", $status))
  };
}

class CasePDF extends FPDF {
  function Header() {
    $this->SetFont('Arial', 'B', 16);
    $this->Cell(0, 10, 'SBMS Case Resolution Report', 0, 1, 'C');
    $this->Ln(2);
  }

  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 9);
    $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
  }

  function SectionTitle($title) {
    $this->Ln(4);
    $this->SetFont('Arial', 'B', 12);
    $this->Cell(0, 8, $title, 0, 1);
    $this->SetDrawColor(180, 180, 180);
    $this->Line(10, $this->GetY(), 200, $this->GetY());
    $this->Ln(3);
  }

  function KV($key, $value) {
    $this->SetFont('Arial', 'B', 10);
    $this->Cell(48, 8, $key . ':', 0, 0);
    $this->SetFont('Arial', '', 10);
    $this->MultiCell(0, 8, (string)$value, 0, 1);
  }
}

$pdf = new CasePDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

$pdf->SectionTitle("Student Report Information");
$pdf->KV("Case ID", $case["case_id"]);
$pdf->KV("Student Name", trim(($case["first_name"] ?? "") . " " . ($case["last_name"] ?? "")));
$pdf->KV("Student Email", $case["email"] ?? "-");
$pdf->KV("Roll Number", $case["roll_number"] ?? "-");
$pdf->KV("Admission Year", $case["admission_year"] ?? "-");
$pdf->KV("Incident Type", $case["incident_type"] ?? "-");
$pdf->KV("Severity", $case["severity"] ?? "-");
$pdf->KV("Occurrence Date/Time", $case["occurrence_datetime"] ?? "-");
$pdf->KV("Location", $case["location"] ?? "-");
$pdf->KV("Submitted At", $case["submitted_at"] ?? "-");

$pdf->SectionTitle("Student Description");
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 8, $case["description"] ?: "-", 0, 'L');

$pdf->SectionTitle("Evidence Submitted By Student");
if (!$evidence) {
  $pdf->SetFont('Arial', '', 10);
  $pdf->MultiCell(0, 8, "No evidence uploaded.", 0, 'L');
} else {
  foreach ($evidence as $i => $ev) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, "Evidence " . ($i + 1), 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(
      0,
      8,
      "Original Name: " . ($ev["original_name"] ?? "-") . "\n" .
      "Stored File: " . ($ev["file_name"] ?? "-") . "\n" .
      "Uploaded At: " . ($ev["uploaded_at"] ?? "-"),
      0,
      'L'
    );
    $pdf->Ln(1);
  }
}

$pdf->SectionTitle("Teacher Work On Case");
$pdf->KV("Assigned Teacher", $case["teacher_name"] ?? "-");
$pdf->KV("Final Status", status_label($case["status"] ?? ""));
$pdf->KV("Final Teacher Note", $case["teacher_note"] ?: "-");

$pdf->SectionTitle("Teacher Status Update History");
if (!$history) {
  $pdf->SetFont('Arial', '', 10);
  $pdf->MultiCell(0, 8, "No teacher status history found.", 0, 'L');
} else {
  foreach ($history as $i => $row) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, "History Step " . ($i + 1), 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $old = trim((string)($row["old_status"] ?? ""));
    $new = trim((string)($row["new_status"] ?? ""));
    $teacher = trim((string)($row["teacher_name"] ?? ""));

    $pdf->MultiCell(
      0,
      8,
      "Changed At: " . ($row["changed_at"] ?? "-") . "\n" .
      "Teacher: " . ($teacher !== "" ? $teacher : "-") . "\n" .
      "Old Status: " . ($old !== "" ? status_label($old) : "-") . "\n" .
      "New Status: " . ($new !== "" ? status_label($new) : "-") . "\n" .
      "Teacher Note: " . (($row["teacher_note"] ?? "") !== "" ? $row["teacher_note"] : "-"),
      0,
      'L'
    );
    $pdf->Ln(2);
  }
}

$pdf->SectionTitle("Teacher Follow-up / Reminder Work");
if (!$followups) {
  $pdf->SetFont('Arial', '', 10);
  $pdf->MultiCell(0, 8, "No follow-up actions recorded.", 0, 'L');
} else {
  foreach ($followups as $i => $row) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, "Follow-up " . ($i + 1), 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(
      0,
      8,
      "Teacher: " . (($row["teacher_name"] ?? "") !== "" ? $row["teacher_name"] : "-") . "\n" .
      "Follow-up Date/Time: " . ($row["followup_datetime"] ?? "-") . "\n" .
      "Note: " . (($row["note"] ?? "") !== "" ? $row["note"] : "-"),
      0,
      'L'
    );
    $pdf->Ln(2);
  }
}

$pdf->SectionTitle("Resolution Summary");
$pdf->SetFont('Arial', '', 10);
$resolutionText =
  "This case has been marked as RESOLVED.\n\n" .
  "The report above contains the original information submitted by the student, " .
  "along with the teacher's investigation and work history, including status updates, notes, " .
  "follow-up actions, and the final resolution note.";
$pdf->MultiCell(0, 8, $resolutionText, 0, 'L');

$filename = "case_resolution_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $case["case_id"]) . ".pdf";
$pdf->Output('D', $filename);
exit;