<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";
$email_value = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $email_value = $email;

    if ($email === "" || $password === "") {
        $message = "All fields are required.";
        $message_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $message_type = "error";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO teachers (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hash);

        try {
            $stmt->execute();
            $message = "Teacher added successfully!";
            $message_type = "success";
            $email_value = "";
        } catch (mysqli_sql_exception $e) {
            $message = "Email already exists.";
            $message_type = "error";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Add Teacher | SBMS</title>

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
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="add_teacher.php" class="active">Add Teacher</a>
      <a class="btn outline" href="login.php">Back to Login</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">

    <div class="card admin-form-card">
      <div class="form-head">
        <div>
          <h2>Add Teacher Account</h2>
          <p class="muted">Create login credentials for teacher panel access.</p>
        </div>
        
      </div>

      <?php if ($message): ?>
        <div class="alert <?php echo htmlspecialchars($message_type); ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="form-grid">

        <div class="field">
          <label>Teacher Email</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($email_value); ?>" required placeholder="teacher@example.com">
          <small class="hint">Use the email the teacher will use to login.</small>
        </div>

        <div class="field">
          <label>Temporary Password</label>
          <input type="password" name="password" required placeholder="Min 8 characters">
          <small class="hint">Ask teacher to change after first login (future feature).</small>
        </div>

        <div class="form-actions">
          <button class="btn primary" type="submit">Create Teacher</button>
          <a class="btn outline" href="admin_dashboard.php">Back to Dashboard</a>
        </div>

      </form>
    </div>

    <div class="footer">Admin tools · System Management · Security & Access Control</div>

  </div>
</div>

</body>
</html>
