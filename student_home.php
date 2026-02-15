<?php
require_once "auth.php";
require_role("student");

$name = $_SESSION["user"]["name"] ?? ($_SESSION["student_name"] ?? "Student");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Student Home | SBMS</title>
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
      <span>SafeSchool</span>
    </div>
    <div class="links">
      <a href="#">Report</a>
      <a href="#">Track</a>
      <a href="#">Get Help</a>
      <a href="#">Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Report Bullying Safely & Privately</h1>
      <p>Welcome, <strong><?php echo htmlspecialchars($name); ?></strong>. Submit anonymous reports and track your case.</p>

      <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:10px;">
        <a class="btn primary" href="#">Report Now</a>
        <a class="btn outline" href="#">Urgent Help</a>
      </div>

      <div class="pills">
        <div class="pill">Anonymous</div>
        <div class="pill">Evidence Upload</div>
        <div class="pill">Ticket Tracking</div>
        <div class="pill">Counsellor Support</div>
      </div>
    </div>

    <div class="steps">
      <div class="step">
        <div class="num">1</div>
        <div>
          <div style="font-weight:900;">Submit Report</div>
          <div style="color:var(--muted); margin-top:4px;">Describe incident details clearly.</div>
        </div>
      </div>
      <div class="step">
        <div class="num">2</div>
        <div>
          <div style="font-weight:900;">Case Review</div>
          <div style="color:var(--muted); margin-top:4px;">Your school reviews and updates status.</div>
        </div>
      </div>
      <div class="step">
        <div class="num">3</div>
        <div>
          <div style="font-weight:900;">Get Support</div>
          <div style="color:var(--muted); margin-top:4px;">Access help resources and guidance.</div>
        </div>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <h3>Report Bullying</h3>
        <p>Start a new case report (anonymous option can be enabled).</p>
        <div style="margin-top:12px;">
          <a class="btn primary" style="width:100%;" href="#">Start Report</a>
        </div>
      </div>

      <div class="card">
        <h3>Track Your Report</h3>
        <p>Track case progress using your tracking ID.</p>
        <div style="margin-top:12px;">
          <a class="btn outline" style="width:100%;" href="#">Track Case</a>
        </div>
      </div>

      <div class="card">
        <h3>Get Support</h3>
        <p>See resources for safety and counselling support.</p>
        <div style="margin-top:12px;">
          <a class="btn outline" style="width:100%;" href="#">View Resources</a>
        </div>
      </div>
    </div>

    <div class="footer">Safety Policy · Helpline: 122-456-7890 · Privacy & Terms</div>
  </div>
</div>

</body>
</html>
