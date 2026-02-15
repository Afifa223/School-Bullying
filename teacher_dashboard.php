<?php
require_once "auth.php";
require_role("teacher");

$name = current_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Teacher Dashboard | SBMS</title>
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
      <span>SBMS Teacher Panel</span>
    </div>
    <div class="links">
      <a href="teacher_submitted.php">Submitted</a>
      <a href="teacher_urgent.php">Urgent</a>
      <a href="teacher_reminders.php">Reminders</a>
      <a href="teacher_filters.php">Filters</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Teacher / Administrative Functionalities</h1>
      <p>Welcome, <strong><?php echo $name; ?></strong>. Review cases, view evidence, update status, and filter by severity/type/location.</p>
    </div>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-top:16px;">
      <div class="card">
        <h3>Submitted Reports</h3>
        <p>All reports submitted by students.</p>
        <a class="btn outline" href="teacher_submitted.php" style="margin-top:10px; display:inline-block;">Open</a>
      </div>

      <div class="card">
        <h3>Urgent Cases</h3>
        <p>High severity cases needing quick action.</p>
        <a class="btn outline" href="teacher_urgent.php" style="margin-top:10px; display:inline-block;">Open</a>
      </div>

      <div class="card">
        <h3>Follow-up Reminders</h3>
        <p>Cases waiting for follow-up updates.</p>
        <a class="btn outline" href="teacher_reminders.php" style="margin-top:10px; display:inline-block;">Open</a>
      </div>
    </div>

    <div class="card" style="margin-top:16px;">
      <h3>Case Review & Management</h3>
      <p style="margin-top:6px;">
        View incident details, severity and location, evidence (images for cyberbullying), and update case status.
      </p>

      <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
        <a class="btn primary" href="teacher_view_case.php">View Detailed Case</a>
        <a class="btn outline" href="teacher_update_status.php">Update Status</a>
        <a class="btn outline" href="teacher_filter_cases.php">Filter Cases</a>
      </div>
    </div>

    <div class="footer">Teacher tools · Severity / Type / Location / Status Filters · Privacy & Terms</div>
  </div>
</div>

</body>
</html>
