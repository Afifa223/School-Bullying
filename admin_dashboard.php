<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$name = current_user_name();

/* Dashboard Stats */
$totalTeachers = 0;
$recentTeachers = [];

try {
    // total teachers count
    $res = $conn->query("SELECT COUNT(*) AS total FROM teachers");
    $totalTeachers = (int)$res->fetch_assoc()["total"];

    // recent 5 teachers
    $res2 = $conn->query("SELECT full_name, email, created_at
                          FROM teachers
                          ORDER BY teacher_id DESC
                          LIMIT 5");
    while ($row = $res2->fetch_assoc()) {
        $recentTeachers[] = $row;
    }
} catch (Exception $e) {
    // If DB issue occurs, the dashboard will still load
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Dashboard | SBMS</title>

  <link rel="stylesheet" href="css/admin.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    .mini-list{ margin-top: 10px; }
    .mini-item{
      display:flex;
      justify-content: space-between;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.10);
      background: rgba(255,255,255,0.05);
      margin-top: 10px;
    }
    .mini-item b{ color: rgba(255,255,255,0.92); }
    .mini-item small{ color: rgba(255,255,255,0.62); }
    .mini-right{ text-align:right; }
  </style>
</head>

<body class="admin-ui">

<div class="nav">
  <div class="container nav-inner">
    <div class="brand">
      <span class="badge admin"></span>
      <span>SBMS Admin Panel</span>
    </div>

    <div class="links">
      <a href="admin_dashboard.php" class="active">Dashboard</a>
      <a href="add_teacher.php">Add Teacher</a>
      <a href="admin_teachers_list.php">Teachers</a>
      <a href="admin_teacher_progress.php">Progress</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">

    <div class="hero-card admin-hero">
      <div>
        <h1>Admin Control Center</h1>
        <p>Welcome, <strong><?php echo $name; ?></strong>. Manage teacher accounts, monitor activity and keep the system running smoothly.</p>
      </div>

      <div class="stat">
        <div class="stat-label">Teachers</div>
        <div class="stat-value"><?php echo $totalTeachers; ?> Total</div>
        <div class="stat-sub">Total teacher accounts created in the system.</div>
      </div>
    </div>

    <div class="grid admin-grid">
      <div class="card admin-card">
        <div class="card-top">
          <h3>Add Teacher</h3>
          <span class="chip">Account</span>
        </div>
        <p>Create new teacher credentials to access the teacher panel.</p>
        <a class="btn primary full" href="add_teacher.php">Open</a>
      </div>

      <div class="card admin-card">
        <div class="card-top">
          <h3>Teachers List</h3>
          <span class="chip">Directory</span>
        </div>
        <p>View all teacher accounts and verify emails.</p>
        <a class="btn primary full" href="admin_teachers_list.php">Open</a>
      </div>

      <div class="card admin-card">
        <div class="card-top">
          <h3>Teacher Progress</h3>
          <span class="chip">Reports</span>
        </div>
        <p>Review performance and case-handling progress.</p>
        <a class="btn primary full" href="admin_teacher_progress.php">Open</a>
      </div>
    </div>

    <!-- Recent Teachers -->
    <div class="card admin-wide">
      <h3>Recent Teachers</h3>
      <p class="muted">Last 5 recently created teachers.</p>

      <div class="mini-list">
        <?php if (count($recentTeachers) === 0): ?>
          <div class="alert">No teacher data found yet.</div>
        <?php else: ?>
          <?php foreach ($recentTeachers as $t): ?>
            <div class="mini-item">
              <div>
                <b><?php echo htmlspecialchars($t["full_name"]); ?></b><br>
                <small><?php echo htmlspecialchars($t["email"]); ?></small>
              </div>
              <div class="mini-right">
                <small><?php echo htmlspecialchars($t["created_at"]); ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="admin-actions" style="margin-top:14px;">
        <a class="btn outline" href="remove_teacher.php">Remove Teacher</a>
        <a class="btn outline" href="admin_teacher_progress.php">View Reports</a>
      </div>
    </div>

    <div class="footer">Admin tools · System Management · Security & Access Control</div>

  </div>
</div>

</body>
</html>