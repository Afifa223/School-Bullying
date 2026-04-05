<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$latestTeacher = null;

try {
    $res = $conn->query("SELECT teacher_id, full_name, email, created_at
                         FROM teachers
                         ORDER BY teacher_id DESC
                         LIMIT 1");
    $latestTeacher = $res->fetch_assoc();
} catch (Exception $e) {
    // page will still load
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Latest Teacher | SBMS</title>

  <link rel="stylesheet" href="css/admin.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    .details-card{
      max-width: 760px;
      margin: 0 auto;
    }
    .detail-box{
      margin-top: 14px;
      padding: 14px 16px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.10);
      background: rgba(255,255,255,0.05);
      color: rgba(255,255,255,0.85);
    }
    .detail-box + .detail-box{
      margin-top: 10px;
    }
    .detail-box b{
      color: rgba(255,255,255,0.95);
    }
    .page-actions{
      display:flex;
      gap:12px;
      margin-top:16px;
      flex-wrap:wrap;
    }
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
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="add_teacher.php">Add Teacher</a>
      <a href="admin_teachers_list.php">Teachers</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">
    <div class="card details-card">
      <h2>Latest Teacher Account</h2>
      <p class="muted">View the most recently created teacher account details.</p>

      <?php if ($latestTeacher): ?>
        <div class="detail-box"><b>Teacher ID:</b> <?php echo htmlspecialchars($latestTeacher["teacher_id"]); ?></div>
        <div class="detail-box"><b>Name:</b> <?php echo htmlspecialchars($latestTeacher["full_name"]); ?></div>
        <div class="detail-box"><b>Email:</b> <?php echo htmlspecialchars($latestTeacher["email"]); ?></div>
        <div class="detail-box"><b>Created:</b> <?php echo date("d M Y, h:i A", strtotime($latestTeacher["created_at"])); ?></div>

        <div class="page-actions">
          <a class="btn primary" href="admin_dashboard.php">Back to Dashboard</a>
          <a class="btn outline" href="admin_teachers_list.php">Back to Teachers</a>
        </div>
      <?php else: ?>
        <div class="alert" style="margin-top:12px;">No teacher account found yet.</div>
      <?php endif; ?>
    </div>

    <div class="footer">Admin tools · Latest Teacher Account</div>
  </div>
</div>

</body>
</html>