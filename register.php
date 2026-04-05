<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";
$redirect = false;

$values = [
  "first_name" => "",
  "last_name"  => "",
  "email"      => ""
];

// Password validation
function valid_password(string $pw): bool {
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

  if ($first_name === "" || $last_name === "" || $email === "" || $password === "" || $confirm === "") {
    $message = "All fields are required.";
    $message_type = "error";
  }
  elseif (!preg_match('/^[0-9]{7}@gmail\.com$/', $email)) {
    $message = "Email must be 7 digits followed by @gmail.com.";
    $message_type = "error";
  }
  elseif ($password !== $confirm) {
    $message = "Passwords do not match.";
    $message_type = "error";
  }
  elseif (!valid_password($password)) {
    $message = "Password must be strong (8+ chars, uppercase, lowercase, number, special char).";
    $message_type = "error";
  }
  else {

    $digits = substr($email, 0, 7);
    $admission_year = (int) substr($digits, 0, 4);
    $roll_number    = (int) substr($digits, 4, 3);

    if ($admission_year < 2000 || $admission_year > (int)date("Y")) {
      $message = "Invalid admission year.";
      $message_type = "error";
    } elseif ($roll_number < 1 || $roll_number > 100) {
      $message = "Roll must be 001–100.";
      $message_type = "error";
    } else {

      $hash = password_hash($password, PASSWORD_DEFAULT);

      try {
        $sql = "INSERT INTO student (first_name, last_name, admission_year, roll_number, email, password)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiss", $first_name, $last_name, $admission_year, $roll_number, $email, $hash);
        $stmt->execute();
        $stmt->close();

        $message = "Registration successful! Redirecting to login...";
        $message_type = "success";
        $redirect = true;

        $values = ["first_name" => "", "last_name" => "", "email" => ""];
      } catch (mysqli_sql_exception $e) {
        if ((int)$e->getCode() === 1062) {
          $message = "Email already registered.";
          $message_type = "error";
        } else {
          $message = "Error: " . $e->getMessage();
          $message_type = "error";
        }
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>

  <link rel="stylesheet" href="css/login.css">

  <?php if ($redirect): ?>
    <!-- AUTO REDIRECT AFTER 2 SECONDS -->
    <meta http-equiv="refresh" content="2;url=login.php">
  <?php endif; ?>

</head>
<body>

<div class="bg-overlay"></div>

<div class="page">
  <header class="page-header">
    <h1 class="system-title">School Bullying Management System</h1>
    <h2 class="page-title">Student Registration</h2>
  </header>

  <div class="container">
    <div class="card">

      <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <label>First Name</label>
        <input type="text" name="first_name"
          value="<?php echo htmlspecialchars($values["first_name"]); ?>" required>

        <label>Last Name</label>
        <input type="text" name="last_name"
          value="<?php echo htmlspecialchars($values["last_name"]); ?>" required>

        <label>Email</label>
        <input type="text" name="email"
          value="<?php echo htmlspecialchars($values["email"]); ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button class="btn" type="submit">Create Account</button>
      </form>

    </div>
  </div>
</div>

</body>
</html>