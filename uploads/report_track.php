<?php
require_once "auth.php";
require_role("student");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$student_id = current_user_id();
$case_id = trim($_GET["case_id"] ?? "");

if ($case_id === "") {
  header("Location: my_reports.php");
  exit;
}

function status_steps(): array {
  return [
    "submitted" => "Submitted",
    "seen" => "Seen by Teacher",
    "under_review" => "Under Review",
    "resolved" => "Resolved",
  ];
}

function progress_percent(string $status): int {
  $keys = array_keys(status_steps());
  $idx = array_search($status, $keys, true);
  if ($idx === false) $idx = 0;
  $max = max(count($keys) - 1, 1);
  return (int)round(($idx / $max) * 100);
}

function days_ago_label(?string $submitted_at): string {
  if (!$submitted_at) return "—";
  try {
    $sub = new DateTime($submitted_at);
    $now = new DateTime();
    $diff = $now->diff($sub);
    if ($diff->days === 0) return "Today";
    if ($diff->days === 1) return "1 day ago";
    return $diff->days . " days ago";
  } catch (Exception $e) {
    return "—";
  }
}

function generate_title(array $r): string {
  $incident = ucfirst(str_replace("_", " ", $r["incident_type"] ?? "incident"));
  $sev = ucfirst($r["severity"] ?? "");
  $loc = trim($r["location"] ?? "");
  $base = trim("$sev $incident");
  if ($loc !== "") $base .= " • " . $loc;
  return $base !== "" ? $base : "Bullying Report";
}

// Fetch report (only if belongs to this student)
$stmt = $conn->prepare("
  SELECT report_id, case_id, is_anonymous, incident_type, severity, occurrence_datetime,
         location, description, status, submitted_at
  FROM bullying_reports
  WHERE case_id = ? AND owner_student_id = ?
  LIMIT 1
");
$stmt->bind_param("si", $case_id, $student_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$report) {
  header("Location: my_reports.php");
  exit;
}

// Evidence
$ev = $conn->prepare("
  SELECT file_name, original_name, uploaded_at
  FROM report_evidence
  WHERE report_id = ?
  ORDER BY evidence_id DESC
");
$ev->bind_param("i", $report["report_id"]);
$ev->execute();
$evidence = $ev->get_result()->fetch_all(MYSQLI_ASSOC);
$ev->close();

$status = $report["status"] ?: "submitted";
$steps = status_steps();
$keys = array_keys($steps);
$currentIndex = array_search($status, $keys, true);
if ($currentIndex === false) $currentIndex = 0;

$title = generate_title($report);
$daysAgo = days_ago_label($report["submitted_at"]);
$percent = progress_percent($status);
$statusLabel = $steps[$status] ?? "Submitted";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Track Case <?php echo htmlspecialchars($report["case_id"]); ?> | SBMS</title>
  <link rel="stylesheet" href="css/report_track.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- NAV -->
<div class="nav">
  <div class="container nav-inner">
    <div class="brand">
      <span class="badge"></span>
      <span>SBMS Student Panel</span>
    </div>
    <div class="links">
      <a href="student_dashboard.php">Home</a>
      <a href="student_report.php">Report</a>
      <a href="my_reports.php" class="active">My Reports</a>
      <a href="help_resources.php">Help & Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="page">
  <div class="container">

    <div class="topbar">
      <a class="btn outline" href="my_reports.php">← Back</a>
      <div class="topmeta">
        <div class="case-title"><?php echo htmlspecialchars($title); ?></div>
        <div class="sub">
          <span class="pill">Case ID: <b><?php echo htmlspecialchars($report["case_id"]); ?></b></span>
          <span class="pill"><?php echo htmlspecialchars($daysAgo); ?></span>
          <span class="pill">Status: <b><?php echo htmlspecialchars($statusLabel); ?></b></span>
        </div>
      </div>
    </div>

    <div class="layout">
      <!-- LEFT: Progress -->
      <div class="card">
        <h2>Progress</h2>

        <div class="progress-row">
          <div class="progress-bar"><div class="progress-fill" style="width:<?php echo (int)$percent; ?>%"></div></div>
          <div class="progress-pct"><?php echo (int)$percent; ?>%</div>
        </div>

        <div class="timeline">
          <?php foreach ($keys as $i => $k): 
            $state = ($i < $currentIndex) ? "done" : (($i === $currentIndex) ? "active" : "todo");
          ?>
            <div class="t-row <?php echo $state; ?>">
              <div class="t-dot"></div>
              <div class="t-box">
                <div class="t-title"><?php echo htmlspecialchars($steps[$k]); ?></div>
                <div class="t-sub">
                  <?php
                    echo match ($k) {
                      "submitted" => "Your report has been submitted successfully.",
                      "seen" => "Teacher will review your report soon.",
                      "under_review" => "Investigation/verification is in progress.",
                      "resolved" => "Case has been resolved and closed.",
                      default => ""
                    };
                  ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="note">
          For now, all cases stay as <b>Submitted</b>. When the teacher dashboard is added, status will update here automatically.
        </div>
      </div>

      <!-- RIGHT: Details -->
      <div class="card">
        <h2>Case Details</h2>

        <div class="kv">
          <div class="k">Incident Type</div>
          <div class="v"><?php echo htmlspecialchars($report["incident_type"]); ?></div>
        </div>
        <div class="kv">
          <div class="k">Severity</div>
          <div class="v"><?php echo htmlspecialchars($report["severity"]); ?></div>
        </div>
        <div class="kv">
          <div class="k">Occurrence</div>
          <div class="v"><?php echo htmlspecialchars($report["occurrence_datetime"]); ?></div>
        </div>
        <div class="kv">
          <div class="k">Location</div>
          <div class="v"><?php echo htmlspecialchars($report["location"]); ?></div>
        </div>
        <div class="kv">
          <div class="k">Submitted At</div>
          <div class="v"><?php echo htmlspecialchars($report["submitted_at"]); ?></div>
        </div>
        <div class="kv">
          <div class="k">Mode</div>
          <div class="v"><?php echo ((int)$report["is_anonymous"]===1) ? "Anonymous" : "Standard"; ?></div>
        </div>

        <div class="desc">
          <div class="k">Description</div>
          <div class="desc-box"><?php echo nl2br(htmlspecialchars($report["description"])); ?></div>
        </div>

        <h2 style="margin-top:16px;">Evidence</h2>
        <?php if (!$evidence): ?>
          <div class="muted">No evidence uploaded.</div>
        <?php else: ?>
          <div class="ev-grid">
            <?php foreach ($evidence as $e): ?>
              <a class="ev-item" href="<?php echo "uploads/" . rawurlencode($e["file_name"]); ?>" target="_blank" rel="noopener">
                <div class="ev-name"><?php echo htmlspecialchars($e["original_name"]); ?></div>
                <div class="ev-date"><?php echo htmlspecialchars($e["uploaded_at"]); ?></div>
                <div class="ev-open">View</div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <div class="footer">Track updates here · SBMS</div>
  </div>
</div>

</body>
</html>