<?php
require_once "auth.php";
require_role("student");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$student_id = current_user_id();

/*
 // DEBUG (optional): session id ঠিক আসছে কিনা দেখতে চাইলে uncomment করো
 // var_dump($student_id); exit;
*/

// Load student information from DB
$stmt = $conn->prepare("
  SELECT first_name, last_name, email, admission_year, roll_number
  FROM student
  WHERE student_id = ?
");  
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$name = $student ? ($student["first_name"] . " " . $student["last_name"]) : current_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Student Home | SBMS</title>

  <link rel="stylesheet" href="css/student_dashboard.css">
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
      <a href="student_dashboard.php" class="active">Home</a>
      <a href="student_report.php">Start Report</a>
      <a href="student_track.php">Track</a>
      <a href="my_report.php">My Reports</a>
      <a href="help_resources.php">Help & Resources</a>

      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<!-- BODY -->
<div class="hero">
  <div class="container">

    <!-- HERO CARD -->
    <div class="hero-card">
      <h1>Report Bullying Safely & Privately</h1>
      <p>Welcome, <strong><?php echo htmlspecialchars($name); ?></strong>.</p>

      <?php if ($student): ?>
        <div class="student-info">
          <div><strong>Email:</strong> <?php echo htmlspecialchars($student["email"]); ?></div>
          <div><strong>Admission Year:</strong> <?php echo htmlspecialchars($student["admission_year"] ?? "-"); ?></div>
          <div><strong>Roll Number:</strong> <?php echo htmlspecialchars($student["roll_number"] ?? "-"); ?></div>
        </div>
      <?php endif; ?>

      <!-- Feature Slider (Advertisement Style) -->
      <div class="feature-slider" aria-label="SBMS features slider">
        <div class="feature-track">

          <div class="feature-card" style="background-image:url('https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=1200&q=60');">
            <div class="feature-overlay"></div>
            <div class="feature-title">Anonymous Option</div>
          </div>

          <div class="feature-card" style="background-image:url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=60');">
            <div class="feature-overlay"></div>
            <div class="feature-title">Evidence Upload</div>
          </div>

          <div class="feature-card" style="background-image:url('https://images.unsplash.com/photo-1556155092-8707de31f9c4?auto=format&fit=crop&w=1200&q=60');">
            <div class="feature-overlay"></div>
            <div class="feature-title">Case ID Tracking</div>
          </div>

          <div class="feature-card" style="background-image:url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=60');">
            <div class="feature-overlay"></div>
            <div class="feature-title">Support Resources</div>
          </div>

        </div>

        <!-- Dots -->
        <div class="feature-dots" aria-hidden="true">
          <span class="dot active"></span>
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
      </div>
    </div>
    <!-- /HERO CARD -->

    <!-- GRID -->
    <div class="grid">
      <div class="card">
        <h3>Submit a Bullying Report</h3>
        <p>Fill a structured report form and (optionally) attach evidence images.</p>
        <div class="card-action">
          <a class="btn primary full" href="student_report.php">Start Report</a>
        </div>
      </div>

      <div class="card">
        <h3>Track Your Report</h3>
        <p>Enter your Case ID to see the status: submitted / under-review / resolved.</p>
        <div class="card-action">
          <a class="btn outline full" href="student_track.php">Track Case</a>
        </div>
      </div>

      <div class="card">
        <h3>Your Reports</h3>
        <p>See all the history of your submitted reports.</p>
        <div class="card-action">
          <a class="btn outline full" href="my_report.php">View Reports</a>
        </div>
      </div>

      <div class="card">
        <h3>Help & Resources</h3>
        <p>Emergency contacts, counselling info, and tips for safety.</p>
        <div class="card-action">
          <a class="btn outline full" href="help_resources.php">View</a>
        </div>
      </div>
    </div>

    <div class="footer">Privacy & Safety · SBMS</div>

  </div>
</div>

<!-- Slider JS -->
<script>
(function(){
  const track = document.querySelector('.feature-track');
  const cards = document.querySelectorAll('.feature-card');
  const dots  = document.querySelectorAll('.feature-dots .dot');
  const slider = document.querySelector('.feature-slider');

  if(!track || cards.length === 0) return;

  let index = 0;
  const gap = 12;

  function slideTo(i){
    index = i;
    const cardWidth = cards[0].getBoundingClientRect().width;
    const x = (cardWidth + gap) * index;
    track.style.transform = `translateX(${-x}px)`;

    dots.forEach(d => d.classList.remove('active'));
    if(dots[index]) dots[index].classList.add('active');
  }

  // Auto slide
  let timer = setInterval(() => {
    const maxIndex = cards.length - 1;
    slideTo(index >= maxIndex ? 0 : index + 1);
  }, 2500);

  // Pause on hover
  if(slider){
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', () => {
      timer = setInterval(() => {
        const maxIndex = cards.length - 1;
        slideTo(index >= maxIndex ? 0 : index + 1);
      }, 2500);
    });
  }

  // Click dots
  dots.forEach((dot, i) => dot.addEventListener('click', () => slideTo(i)));

  // Start
  slideTo(0);
})();
</script>

</body>
</html>