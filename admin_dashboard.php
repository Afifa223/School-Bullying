<?php
require_once "auth.php";
require_role("admin");

$name = current_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Dashboard | SBMS</title>

  <!-- Admin theme only (dark) -->
  <link rel="stylesheet" href="css/admin.css">

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
        <div class="stat-label">Quick Tip</div>
        <div class="stat-value">Keep strong passwords 🔐</div>
        <div class="stat-sub">Use 8+ chars, uppercase, number & special char.</div>
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
        <a class="btn outline full" href="admin_teachers_list.php">Open</a>
      </div>

      <div class="card admin-card">
        <div class="card-top">
          <h3>Teacher Progress</h3>
          <span class="chip">Reports</span>
        </div>
        <p>Review performance and case-handling progress.</p>
        <a class="btn outline full" href="admin_teacher_progress.php">Open</a>
      </div>
    </div>

    <div class="card admin-wide">
      <h3>Admin Tools</h3>
      <p class="muted">Jump into frequently used actions.</p>

      <div class="admin-actions">
        <a class="btn outline" href="remove_teacher.php">Remove Teacher</a>
        <a class="btn outline" href="admin_teachers_list.php">Manage Teachers</a>
        <a class="btn outline" href="admin_teacher_progress.php">View Reports</a>
      </div>
    </div>

    <div class="footer">Admin tools · System Management · Security & Access Control</div>

  </div>
</div>

</body>
</html>
