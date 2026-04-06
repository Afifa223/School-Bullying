<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$name = current_user_name();
$teacherProgress = [];

function status_steps(): array {
    return [
        "submitted"    => "Submitted",
        "seen"         => "Seen by Teacher",
        "under_review" => "Under Review",
        "action_taken" => "Action Taken",
        "resolved"     => "Resolved",
    ];
}

function progress_percent(string $status): int {
    $keys = array_keys(status_steps());
    $idx = array_search($status, $keys, true);
    if ($idx === false) $idx = 0;
    $max = max(count($keys) - 1, 1);
    return (int)round(($idx / $max) * 100);
}

try {
    $sql = "
      SELECT
        t.teacher_id,
        t.full_name,
        t.email,
        br.case_id,
        br.status
      FROM teachers t
      LEFT JOIN bullying_reports br
        ON br.assigned_teacher_id = t.teacher_id
      ORDER BY t.full_name ASC, br.report_id DESC
    ";

    $res = $conn->query($sql);

    $grouped = [];

    while ($row = $res->fetch_assoc()) {
        $tid = (int)$row["teacher_id"];

        if (!isset($grouped[$tid])) {
            $grouped[$tid] = [
                "teacher_id" => $tid,
                "full_name" => $row["full_name"],
                "email" => $row["email"],
                "total_cases" => 0,
                "resolved_cases" => 0,
                "seen_cases" => 0,
                "review_cases" => 0,
                "action_cases" => 0,
                "total_progress" => 0
            ];
        }

        if (!empty($row["case_id"])) {
            $status = trim((string)$row["status"]);
            if ($status === "") {
                $status = "submitted";
            }

            $grouped[$tid]["total_cases"]++;
            $grouped[$tid]["total_progress"] += progress_percent($status);

            if ($status === "seen") {
                $grouped[$tid]["seen_cases"]++;
            } elseif ($status === "under_review") {
                $grouped[$tid]["review_cases"]++;
            } elseif ($status === "action_taken") {
                $grouped[$tid]["action_cases"]++;
            } elseif ($status === "resolved") {
                $grouped[$tid]["resolved_cases"]++;
            }
        }
    }

    foreach ($grouped as $teacher) {
        $avgProgress = $teacher["total_cases"] > 0
            ? (int)round($teacher["total_progress"] / $teacher["total_cases"])
            : 0;

        $teacherProgress[] = [
            "teacher_id" => $teacher["teacher_id"],
            "full_name" => $teacher["full_name"],
            "email" => $teacher["email"],
            "total_cases" => $teacher["total_cases"],
            "seen_cases" => $teacher["seen_cases"],
            "review_cases" => $teacher["review_cases"],
            "action_cases" => $teacher["action_cases"],
            "resolved_cases" => $teacher["resolved_cases"],
            "progress_percent" => $avgProgress
        ];
    }

} catch (Exception $e) {
    // page still loads
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Teacher Progress | SBMS</title>

  <link rel="stylesheet" href="css/teacher_progress.css?v=2">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-ui">

<div class="nav">
  <div class="container nav-inner">
    <div class="brand">
      <span class="badge admin"></span>
      <span>SBMS Admin Panel</span>
    </div>

    <div class="links">
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="add_teacher.php">Add Teacher</a>
      <a href="admin_teachers_list.php">Teachers</a>
      <a href="teacher_progress.php" class="active">Teacher Progress</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">

    <div class="hero-card page-hero">
      <div>
        <h1>Teacher Progress</h1>
        <p>
          Welcome, <strong><?php echo htmlspecialchars($name); ?></strong>.
          This page shows each assigned teacher's average progress based on the same percentage logic used in report tracking.
        </p>
      </div>
    </div>

    <div class="card legend-card">
      <h3>Progress Calculation</h3>
      <p class="muted">
        Submitted = 0% · Seen = 25% · Under Review = 50% · Action Taken = 75% · Resolved = 100%
      </p>
    </div>

    <?php if (count($teacherProgress) === 0): ?>
      <div class="card empty-card">
        <div class="empty-title">No teacher progress found</div>
        <div class="empty-sub">Teachers or assigned cases are not available yet.</div>
      </div>
    <?php else: ?>
      <div class="progress-grid">
        <?php foreach ($teacherProgress as $tp): ?>
          <div class="progress-card">
            <div class="progress-head">
              <div>
                <h3><?php echo htmlspecialchars($tp["full_name"]); ?></h3>
                <p><?php echo htmlspecialchars($tp["email"]); ?></p>
              </div>
              <div class="progress-badge"><?php echo (int)$tp["progress_percent"]; ?>%</div>
            </div>

            <div class="progress-bar">
              <div class="progress-fill" style="width: <?php echo (int)$tp["progress_percent"]; ?>%;"></div>
            </div>

            <div class="stats-grid">
              <div class="mini-stat">
                <span>Total Cases</span>
                <strong><?php echo (int)$tp["total_cases"]; ?></strong>
              </div>
              <div class="mini-stat">
                <span>Seen</span>
                <strong><?php echo (int)$tp["seen_cases"]; ?></strong>
              </div>
              <div class="mini-stat">
                <span>Under Review</span>
                <strong><?php echo (int)$tp["review_cases"]; ?></strong>
              </div>
              <div class="mini-stat">
                <span>Action Taken</span>
                <strong><?php echo (int)$tp["action_cases"]; ?></strong>
              </div>
              <div class="mini-stat">
                <span>Resolved</span>
                <strong><?php echo (int)$tp["resolved_cases"]; ?></strong>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="footer">Teacher monitoring · Performance overview · SBMS</div>

  </div>
</div>

</body>
</html>