<?php
require_once "auth.php";
require_role("student");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$student_id = current_user_id();

/**
 * Clean + short preview from description
 */
function preview_text(string $text, int $max = 90): string {
  $text = trim(preg_replace('/\s+/', ' ', $text));
  if (mb_strlen($text) <= $max) return $text;
  return mb_substr($text, 0, $max) . "…";
}

// Fetch reports (Case ID + submitted time + type + description)
$stmt = $conn->prepare("
  SELECT case_id, submitted_at, is_anonymous, description
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
  <link rel="stylesheet" href="css/my_report.css">
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
      <a href="student_report.php">Start Report</a>
      <a href="student_track.php">Track</a>
      <a href="my_reports.php" class="active">My Reports</a>
      <a href="help_resources.php">Help & Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="page">
  <div class="container">

    <div class="page-head">
      <div>
        <h1>My Reports</h1>
        <p class="muted">Here are your submitted reports with submission time and a short preview.</p>
      </div>
    </div>

    <div class="card list-card">

      <?php if (!$reports): ?>
        <div class="empty">
          <div class="empty-title">No reports found</div>
          <div class="empty-sub">Submit a bullying report to see it here.</div>
        </div>
      <?php else: ?>

        <div class="table">

          <!-- TABLE HEADER -->
          <div class="trow thead">
            <div>Case ID</div>
            <div>Submitted</div>
            <div>Type</div>
            <div>Description (Preview)</div>
          </div>

          <!-- TABLE BODY -->
          <?php foreach ($reports as $r): ?>
            <?php
              $isAnon = (int)($r["is_anonymous"] ?? 0) === 1;
              $typeLabel = $isAnon ? "Anonymous" : "Standard";
              $typeClass = $isAnon ? "type-anon" : "type-norm";
            ?>
            <div class="trow tbody">
              <div class="caseid"><?php echo htmlspecialchars($r["case_id"]); ?></div>

              <div class="date">
                <?php echo htmlspecialchars($r["submitted_at"] ?? "—"); ?>
              </div>

              <div>
                <span class="type <?php echo $typeClass; ?>">
                  <?php echo htmlspecialchars($typeLabel); ?>
                </span>
              </div>

              <div class="desc-preview">
                <?php echo htmlspecialchars(preview_text((string)($r["description"] ?? ""), 110)); ?>
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