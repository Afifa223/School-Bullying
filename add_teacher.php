<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";
$name_value = "";
$email_value = "";
$created_teacher = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";

    $name_value  = $full_name;
    $email_value = $email;

    // Validation
    if ($full_name === "" || $email === "" || $password === "") {
        $message = "All fields are required.";
        $message_type = "error";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "error";
    }
    elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $message_type = "error";
    } 
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO teachers (full_name, email, password) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $full_name, $email, $hash);

        try {
            $stmt->execute();

            $message = "Teacher added successfully!";
            $message_type = "success";

            // Show once after creation
            $created_teacher = [
                "name" => $full_name,
                "email" => $email,
                "password" => $password
            ];

            $name_value = "";
            $email_value = "";
        } 
        catch (mysqli_sql_exception $e) {
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

<?php if ($created_teacher): ?>
  <div class="card" style="margin-top:15px; padding:15px;">
    <h3>Created Teacher Info</h3>
    <p><b>Name:</b> <?php echo htmlspecialchars($created_teacher["name"]); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($created_teacher["email"]); ?></p>
    <p><b>Temporary Password:</b> <?php echo htmlspecialchars($created_teacher["password"]); ?></p>
    <small class="hint">Copy this password and give it to the teacher. It will not be shown again.</small>
  </div>
<?php endif; ?>

<form method="POST" class="form-grid">

<div class="field">
  <label>Teacher Name</label>
  <input type="text" name="full_name"
         value="<?php echo htmlspecialchars($name_value); ?>"
         required placeholder="e.g. Teacher Name">
</div>

<div class="field">
  <label>Teacher Email</label>
  <input type="email" name="email"
         value="<?php echo htmlspecialchars($email_value); ?>"
         required placeholder="teacher@example.com">
</div>

<div class="field">
  <label>Temporary Password</label>
  <input type="password" name="password"
         required placeholder="Min 8 characters">
</div>

<div class="form-actions">
  <button class="btn primary" type="submit">Create Teacher</button>
  <a class="btn outline" href="admin_dashboard.php">Back to Dashboard</a>
</div>

</form>

</div>

<div class="footer">
Admin tools · System Management · Security & Access Control
</div>

</div>
</div>

</body>
</html>