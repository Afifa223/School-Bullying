<?php
require_once "auth.php";
require_role("student");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Help & Resources | SBMS</title>

  <link rel="stylesheet" href="css/help_resources.css">
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
      <a href="my_report.php">My Reports</a>
      <a href="help_resources.php" class="active">Help & Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">

    <div class="page-header">
      <h1>Help & Support Resources</h1>
      <p>Your safety and well-being are our priority.</p>
    </div>

    <!-- Emergency Section -->
    <div class="card">
      <h2>🚨 Emergency Contacts</h2>
      <div class="info-grid">
        <div><strong>National Emergency:</strong> 999</div>
        <div><strong>School Security:</strong> +880 1234 567890</div>
        <div><strong>School Counsellor:</strong> +880 9876 543210</div>
         <div><strong>School Authority:</strong> +880 9876 543210</div>
      </div>
    </div>

    <!-- School Info -->
    <div class="card">
      <h2>🏫 School Contact Information</h2>
      <div class="info-grid">
        <div><strong>Principal Office:</strong> Room 101</div>
        <div><strong>Vice Principal:</strong> Room 103</div>
        <div><strong>Teachers Room:</strong> Room 210</div>
        <div><strong>IT Support:</strong> Room 305</div>
      </div>
    </div>

    <!-- Tips Section -->
    <div class="card">
      <h2>💡 Important Safety Tips</h2>
      <ul>
        <li>Always report bullying incidents immediately.</li>
        <li>Keep evidence (screenshots, messages).</li>
        <li>Stay in groups if you feel unsafe.</li>
        <li>Talk to trusted adults.</li>
        <li>Use the anonymous report option if needed.</li>
      </ul>
    </div>

    <!-- Guidance Section -->
    <div class="card">
      <h2>📘 Guidance & Support</h2>
      <p>
        Bullying can affect mental health. You are not alone. The school provides
        counselling sessions and support groups. Contact the counsellor or submit
        a report through SBMS.
      </p>
    </div>

    <!-- Image Section -->
    <div class="image-grid">
      <div class="image-card">
        <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e" alt="Student Support">
        <div class="image-label">Student Support</div>
      </div>

      <div class="image-card">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f" alt="Counselling">
        <div class="image-label">Counselling</div>
      </div>

      <div class="image-card">
        <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7" alt="Safe School">
        <div class="image-label">Safe School Environment</div>
      </div>
    </div>

    <div class="footer">
      SBMS · Student Safety & Support System
    </div>

  </div>
</div>

</body>
</html>