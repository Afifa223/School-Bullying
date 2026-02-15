<?php
require_once "auth.php";
require_role("teacher");

$name = $_SESSION["user"]["name"] ?? "Teacher";
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
      <a href="#">Submitted</a>
      <a href="#">Urgent</a>
      <a href="#">Reminders</a>
      <a href="#">Filters</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Teacher / Administrative Functionalities</h1>
      <p>Welcome, <strong><?php echo htmlspecialchars($name); ?></strong>. Review cases, view evidence, update status, and filter by severity/type/location.</p>
    </div>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-top:16px;">
      <div class="card">
        <h3>Submitted Reports</h3>
        <p>All reports submitted by students.</p>
        <div style="font-size:28px; font-weight:900; margin-top:10px;">0</div>
      </div>
      <div class="card">
        <h3>Urgent Cases</h3>
        <p>High severity cases needing quick action.</p>
        <div style="font-size:28px; font-weight:900; margin-top:10px; color:#b91c1c;">0</div>
      </div>
      <div class="card">
        <h3>Follow-up Reminders</h3>
        <p>Cases waiting for follow-up updates.</p>
        <div style="font-size:28px; font-weight:900; margin-top:10px; color:var(--primary);">0</div>
      </div>
    </div>

    <div class="card" style="margin-top:16px;">
      <h3>Case Review & Management</h3>
      <p style="margin-top:6px;">
        View incident details, severity and location, evidence (images for cyberbullying), and update case status.
      </p>

      <table class="table">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Incident Type</th>
            <th>Location</th>
            <th>Severity</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>#—</td><td>—</td><td>—</td><td>High</td><td>Under Review</td>
          </tr>
          <tr>
            <td>#—</td><td>—</td><td>—</td><td>Medium</td><td>Action Taken</td>
          </tr>
          <tr>
            <td>#—</td><td>—</td><td>—</td><td>Low</td><td>Resolved</td>
          </tr>
        </tbody>
      </table>

      <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
        <a class="btn primary" href="#">View Detailed Case</a>
        <a class="btn outline" href="#">Update Status</a>
        <a class="btn outline" href="#">Filter Cases</a>
      </div>
    </div>

    <div class="footer">Teacher tools · Severity / Type / Location / Status Filters · Privacy & Terms</div>
  </div>
</div>

</body>
</html>
