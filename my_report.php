<?php
require_once "auth.php";
require_role("student");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$student_id = current_user_id();

function preview_text(string $text, int $max = 90): string {
  $text = trim(preg_replace('/\s+/', ' ', $text));
  if (mb_strlen($text) <= $max) return $text;
  return mb_substr($text, 0, $max) . "…";
}

$stmt = $conn->prepare("
  SELECT case_id, submitted_at, is_anonymous, description, teacher_note, resolved_status
  FROM bullying_reports
  WHERE owner_student_id = ?
  ORDER BY submitted_at DESC, report_id DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>My Reports | SBMS</title>
  <link rel="stylesheet" href="css/my_report.css?v=10">
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
      <a href="student_report.php">Start Report</a>
      <a href="student_track.php">Track</a>
      <a href="my_report.php" class="active">My Reports</a>
      <a href="help_resources.php">Help & Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="page">
  <div class="container">

    <div class="page-head">
      <h1>My Reports</h1>
      <p class="muted">Here are your submitted reports with teacher notes and resolution info.</p>
    </div>

    <div class="card">

      <?php if (!$reports): ?>
        <div class="empty">
          <div class="empty-title">No reports found</div>
          <div class="empty-sub">Submit a bullying report to see it here.</div>
        </div>
      <?php else: ?>

        <div class="table">

          <!-- HEADER -->
          <div class="trow thead">
            <div>Case ID</div>
            <div>Description (Preview)</div>
            <div>Submitted</div>
            <div>Teacher Note</div>
            <div>Resolved Status</div>
            <div>Type</div>
          </div>

          <!-- BODY -->
          <?php foreach ($reports as $r): ?>

            <?php
              $isAnon = (int)$r["is_anonymous"] === 1;
              $typeLabel = $isAnon ? "Anonymous" : "Standard";
              $typeClass = $isAnon ? "type-anon" : "type-norm";
              $teacherNote = trim((string)$r["teacher_note"]);
              $resolvedStatus = trim((string)$r["resolved_status"]);
            ?>

            <div class="trow tbody">

              <!-- 1 Case ID -->
              <div class="caseid">
                <?php echo htmlspecialchars($r["case_id"]); ?>
              </div>

              <!-- 2 Description -->
              <div>
                <?php echo htmlspecialchars(preview_text($r["description"],110)); ?>
              </div>

              <!-- 3 Submitted -->
              <div>
                <?php echo htmlspecialchars($r["submitted_at"]); ?>
              </div>

              <!-- 4 Teacher Note -->
              <div>
                <?php echo htmlspecialchars($teacherNote !== "" ? $teacherNote : "—"); ?>
              </div>

              <!-- 5 Resolved Status -->
              <div class="resolved">
                <?php echo htmlspecialchars($resolvedStatus !== "" ? $resolvedStatus : ""); ?>
              </div>

              <!-- 6 Type -->
              <div>
                <span class="type <?php echo $typeClass; ?>">
                  <?php echo htmlspecialchars($typeLabel); ?>
                </span>
              </div>

            </div>

          <?php endforeach; ?>

        </div>

      <?php endif; ?>

    </div>

    <div class="footer">Only you can see your reports · SBMS</div>

  </div>
</div>

</body>
</html>