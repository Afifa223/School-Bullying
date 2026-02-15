<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";
$values = [
  "first_name" => "",
  "last_name" => "",
  "email" => ""
];

// Password rules: >=8, upper, lower, number, special
function valid_password($pw) {
  if (strlen($pw) < 8) return false;
  if (!preg_match('/[A-Z]/', $pw)) return false;
  if (!preg_match('/[a-z]/', $pw)) return false;
  if (!preg_match('/[0-9]/', $pw)) return false;
  if (!preg_match('/[^A-Za-z0-9]/', $pw)) return false;
  return true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $first_name = trim($_POST["first_name"] ?? "");
  $last_name  = trim($_POST["last_name"] ?? "");
  $email      = trim($_POST["email"] ?? "");
  $password   = $_POST["password"] ?? "";
  $confirm    = $_POST["confirm_password"] ?? "";

  $values["first_name"] = $first_name;
  $values["last_name"]  = $last_name;
  $values["email"]      = $email;

  // Required
  if ($first_name === "" || $last_name === "" || $email === "" || $password === "" || $confirm === "") {
    $message = "All fields are required.";
    $message_type = "error";
  }
  // Student email rule: 7 digits + @gmail.com
  else if (!preg_match('/^[0-9]{7}@gmail\.com$/', $email)) {
    $message = "Email must be 7 digits followed by @gmail.com (example: 2023001@gmail.com).";
    $message_type = "error";
  }
  else if ($password !== $confirm) {
    $message = "Passwords do not match.";
    $message_type = "error";
  }
  else if (!valid_password($password)) {
    $message = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    $message_type = "error";
  }
  else {
    $username = substr($email, 0, 7);          // first 7 digits
    $admission_year = (int)substr($email, 0, 4); // first 4 digits
    $roll_number = (int)substr($email, 4, 3);    // last 3 digits

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
      $stmt = $conn->prepare("
        INSERT INTO student (first_name, last_name, admission_year, roll_number, email, password)
        VALUES (?, ?, ?, ?, ?, ?)
      ");
      $stmt->bind_param("ssiiss", $first_name, $last_name, $admission_year, $roll_number, $email, $hash);
      $stmt->execute();
      $stmt->close();

      $message = "Registration successful! You can now log in.";
      $message_type = "success";

      // Clear fields after success
      $values = ["first_name"=>"", "last_name"=>"", "email"=>""];
    } catch (mysqli_sql_exception $e) {
      // Duplicate email
      if ((int)$e->getCode() === 1062) {
        $message = "This email is already registered. Please log in.";
        $message_type = "error";
      } else {
        $message = "Registration error: " . $e->getMessage();
        $message_type = "error";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>SBMS | Student Registration</title>

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Use your project theme -->
  <link rel="stylesheet" href="css/login.css?v=3" />
</head>
<body>

  <div class="page">
    <header class="page-header">
      <h1 class="system-title">School Bullying Management System</h1>
      <h2 class="page-title">Student Registration</h2>
    </header>

    <div class="container">
      <div class="card">

        <?php if ($message): ?>
          <div class="alert <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <label>First Name</label>
          <input type="text" name="first_name" value="<?php echo htmlspecialchars($values["first_name"]); ?>" required>

          <label>Last Name</label>
          <input type="text" name="last_name" value="<?php echo htmlspecialchars($values["last_name"]); ?>" required>

          <label>Email</label>
          <input type="text" name="email" value="<?php echo htmlspecialchars($values["email"]); ?>" placeholder="2023001@gmail.com" required>

          <label>Password</label>
          <input type="password" name="password" required>

          <label>Confirm Password</label>
          <input type="password" name="confirm_password" required>

          <button class="btn" type="submit">Create Account</button>
        </form>

        <div class="divider"><span>OR</span></div>

        <a class="btn-outline" href="login.php">Back to Login</a>

      </div>
    </div>
  </div>

</body>
</html>
