<?php
require_once "auth.php";
require_role("student");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";
$case_id_created = "";

function generate_case_id(): string {
    // Example: SBMS-20260227-AB12CD
    $datePart = date("Ymd");
    $randPart = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
    return "SBMS-$datePart-$randPart";
}

function clean_text(string $s): string {
    return trim(preg_replace('/\s+/', ' ', $s));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $student_id = current_user_id();

    $is_anonymous = isset($_POST["is_anonymous"]) ? 1 : 0;
    $incident_type = $_POST["incident_type"] ?? "";
    $severity = $_POST["severity"] ?? "";
    $occurrence_datetime = $_POST["occurrence_datetime"] ?? "";
    $location = clean_text($_POST["location"] ?? "");
    $description = clean_text($_POST["description"] ?? "");

    // Basic validation
    $allowed_incidents = ["physical","verbal","cyberbullying","social","other"];
    $allowed_severity = ["low","medium","high"];

    if (!in_array($incident_type, $allowed_incidents, true)) {
        $message = "Invalid incident type.";
        $message_type = "error";
    } elseif (!in_array($severity, $allowed_severity, true)) {
        $message = "Invalid severity level.";
        $message_type = "error";
    } elseif ($occurrence_datetime === "" || $location === "" || $description === "") {
        $message = "Please fill all required fields.";
        $message_type = "error";
    } else {

        // anonymous: store NULL student_id
        $store_student_id = $is_anonymous ? null : $student_id;

        // generate unique case_id (retry if collision)
        $case_id = generate_case_id();
        $tries = 0;
        while ($tries < 5) {
            $chk = $conn->prepare("SELECT report_id FROM bullying_reports WHERE case_id = ?");
            $chk->bind_param("s", $case_id);
            $chk->execute();
            $exists = $chk->get_result()->fetch_assoc();
            $chk->close();
            if (!$exists) break;
            $case_id = generate_case_id();
            $tries++;
        }
        if ($tries >= 5) {
            $message = "Could not generate a unique Case ID. Try again.";
            $message_type = "error";
        } else {


            // Insert report
          $status = "submitted";   // ✅ VERY IMPORTANT LINE

$sql = "INSERT INTO bullying_reports
(case_id, owner_student_id, student_id, is_anonymous,
 incident_type, severity, occurrence_datetime, location, description)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$owner_student_id = $student_id;
$public_student_id = $is_anonymous ? null : $student_id;

$stmt->bind_param(
    "siiisssss",
    $case_id,
    $owner_student_id,
    $public_student_id,
    $is_anonymous,
    $incident_type,
    $severity,
    $occurrence_datetime,
    $location,
    $description
);

$stmt->execute();
$report_id = $stmt->insert_id;
$stmt->close();

            // Handle evidence upload (optional)
            // Accept multiple files: <input name="evidence[]" multiple>
            if (!empty($_FILES["evidence"]) && !empty($_FILES["evidence"]["name"][0])) {

                $upload_dir = __DIR__ . "/uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $allowed_mimes = ["image/jpeg","image/png","image/webp"];
                $max_size = 3 * 1024 * 1024; // 3MB each

                $count = count($_FILES["evidence"]["name"]);
                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES["evidence"]["error"][$i] !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $tmp = $_FILES["evidence"]["tmp_name"][$i];
                    $orig = $_FILES["evidence"]["name"][$i];
                    $size = (int)$_FILES["evidence"]["size"][$i];

                    if ($size <= 0 || $size > $max_size) {
                        continue;
                    }

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tmp);

                    if (!in_array($mime, $allowed_mimes, true)) {
                        continue;
                    }

                    $ext = match ($mime) {
                        "image/jpeg" => "jpg",
                        "image/png"  => "png",
                        "image/webp" => "webp",
                        default      => "bin"
                    };

                    $safe_name = "EV_" . $case_id . "_" . bin2hex(random_bytes(6)) . "." . $ext;
                    $dest = $upload_dir . $safe_name;

                    if (move_uploaded_file($tmp, $dest)) {
                        $ins = $conn->prepare("INSERT INTO report_evidence
                          (report_id, file_name, original_name, mime_type, file_size)
                          VALUES (?, ?, ?, ?, ?)");
                        $ins->bind_param("isssi", $report_id, $safe_name, $orig, $mime, $size);
                        $ins->execute();
                        $ins->close();
                    }
                }
            }

            $message = "Report submitted successfully!";
            $message_type = "success";
            $case_id_created = $case_id;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Submit Report | SBMS</title>
  <link rel="stylesheet" href="css/app.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="nav">
  <div class="container nav-inner">
    <div class="brand">
      <span class="badge"></span>
      <span>SBMS Student Panel</span>
    </div>
    <div class="links">
      <a href="student_dashboard.php">Home</a>
      <a href="student_report.php" class="active">Start Report</a>
      <a href="student_track.php">Track</a>
      <a href="my_report.php">My Reports</a>
      <a href="help_resources.php">Help & Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">

    <div class="card">
      <h2>Submit a Bullying Report</h2>
      <p style="color:var(--muted); margin-top:6px; line-height:1.6;">
        Fill in the details carefully. You can submit anonymously.
      </p>

      <?php if ($message): ?>
        <div class="alert <?php echo htmlspecialchars($message_type); ?>" style="margin-top:12px;">
          <?php echo htmlspecialchars($message); ?>
          <?php if ($message_type === "success" && $case_id_created): ?>
            <div style="margin-top:10px; font-weight:800;">
              Your Case ID: <span style="padding:6px 10px; border-radius:10px; border:1px solid rgba(0,0,0,0.12);">
                <?php echo htmlspecialchars($case_id_created); ?>
              </span>
            </div>
            
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" style="margin-top:14px;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

        <label style="display:block; margin-top:10px;">
          <input type="checkbox" name="is_anonymous" value="1">
          Submit anonymously (your identity will not be attached)
        </label>

        <label style="display:block; margin-top:12px; font-weight:700;">Incident Type</label>
        <select name="incident_type" required style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(15,23,42,0.12);">
          <option value="">Select type</option>
          <option value="physical">Physical</option>
          <option value="verbal">Verbal</option>
          <option value="cyberbullying">Cyberbullying</option>
          <option value="social">Social</option>
          <option value="other">Other</option>
        </select>

        <label style="display:block; margin-top:12px; font-weight:700;">Severity Level</label>
        <select name="severity" required style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(15,23,42,0.12);">
          <option value="">Select severity</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>

        <label style="display:block; margin-top:12px; font-weight:700;">Date & Time of Occurrence</label>
        <input type="datetime-local" name="occurrence_datetime" required style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(15,23,42,0.12);">

        <label style="display:block; margin-top:12px; font-weight:700;">Location</label>
        <input type="text" name="location" required placeholder="Playground, washroom, corridor..." 
               style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(15,23,42,0.12);">

        <label style="display:block; margin-top:12px; font-weight:700;">Detailed Description</label>
        <textarea name="description" required rows="5"
          placeholder="Describe what happened, who was involved, and any important details..."
          style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(15,23,42,0.12);"></textarea>

        <label style="display:block; margin-top:12px; font-weight:700;">Evidence Upload (optional)</label>
        <input type="file" name="evidence[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

        <button class="btn primary" type="submit" style="margin-top:14px;">Submit Report</button>
      </form>
    </div>

  </div>
</div>

</body>
</html>