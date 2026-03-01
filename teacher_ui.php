<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once "db.php";

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function teacher_require_login(): void {
  if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit;
  }
  if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
  }
}

function teacher_name(): string {
  return $_SESSION["user"]["name"] ?? ($_SESSION["user"]["email"] ?? "Teacher");
}

function teacher_id(): int {
  return (int)($_SESSION["user"]["id"] ?? 0);
}

function teacher_header(string $active, string $title = "SBMS Teacher Panel"): void {
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title><?php echo e($title); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/teacher.css?v=1" />
  </head>
  <body>
    <div class="nav">
      <div class="container nav-inner">
        <div class="brand">
          <span class="badge"></span>
          <span>SBMS Teacher Panel</span>
        </div>

        <div class="links">
          <a class="<?php echo $active==='dashboard' ? 'active' : ''; ?>" href="teacher_dashboard.php">Dashboard</a>
          <a class="<?php echo $active==='submitted' ? 'active' : ''; ?>" href="teacher_submitted.php">Submitted</a>
          <a class="<?php echo $active==='urgent' ? 'active' : ''; ?>" href="teacher_urgent.php">Urgent</a>
          <a class="<?php echo $active==='reminders' ? 'active' : ''; ?>" href="teacher_reminders.php">Reminders</a>
          <a class="<?php echo $active==='filters' ? 'active' : ''; ?>" href="teacher_filters.php">Filters</a>
          <a class="btn outline" href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  <?php
}

function teacher_footer(): void {
  ?>
  
  </body>
  </html>
  <?php
}