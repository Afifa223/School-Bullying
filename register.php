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
<<<<<<< HEAD
  $first_name = trim($_POST["first_name"] ?? "");
  $last_name  = trim($_POST["last_name"] ?? "");
  $email      = trim($_POST["email"] ?? "");
  $password   = $_POST["password"] ?? "";
  $confirm    = $_POST["confirm_password"] ?? "";

  $values["first_name"] = $first_name;
  $values["last_name"]  = $last_name;
  $values["email"]      = $email;
=======
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name  = trim($_POST["last_name"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $password   = $_POST["password"] ?? "";
    $confirm    = $_POST["confirm_password"] ?? "";
>>>>>>> 1725bfd (added admin dashboard and create teacher dashboard)

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
<<<<<<< HEAD
      } else {
        $message = "Registration error: " . $e->getMessage();
        $message_type = "error";
      }
=======
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = "error";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $message = "Password must contain at least one uppercase letter.";
        $message_type = "error";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $message = "Password must contain at least one lowercase letter.";
        $message_type = "error";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $message = "Password must contain at least one number.";
        $message_type = "error";
    } elseif (!preg_match('/[\W_]/', $password)) {
        $message = "Password must contain at least one special character.";
        $message_type = "error";
    } elseif (!preg_match('/^[0-9]{7}@gmail\.com$/', $email)) {
        $message = "Student email must be 7 digits followed by @gmail.com";
        $message_type = "error";
    } else {
        $username = substr($email, 0, 7);
        $year = (int) substr($username, 0, 4);
        $roll = (int) substr($username, 4, 3);

        if ($year < 2000 || $year > (int)date("Y")) {
            $message = "Invalid admission year.";
            $message_type = "error";
        } elseif ($roll < 1 || $roll > 100) {
            $message = "Roll number must be between 001 and 100.";
            $message_type = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO student
                    (first_name, last_name, admission_year, roll_number, email, password)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiiss", $first_name, $last_name, $year, $roll, $email, $hashed_password);

            try {
                $stmt->execute();
                $message = "Registration successful!";
                $message_type = "success";
                $email_value = $first_value = $last_value = "";
            } catch (mysqli_sql_exception $e) {
                if ((int)$e->getCode() === 1062) {
                    $message = "This email already exists.";
                } else {
                    $message = "Something went wrong.";
                }
                $message_type = "error";
            }

            $stmt->close();
        }
>>>>>>> 1725bfd (added admin dashboard and create teacher dashboard)
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

<<<<<<< HEAD
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
=======
<div class="page">
<header class="page-header">
  <h1 class="system-title">School Bullying Management System</h1>
  <h2 class="page-title">Student Registration</h2>
</header>

<div class="container">
  <div class="card">

    <?php if ($message): ?>
      <div class="alert <?php echo htmlspecialchars($message_type); ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if ($message_type !== "success"): ?>
      <form method="POST">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_value); ?>" required>

        <label>Last Name</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_value); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email_value); ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button class="btn" type="submit">Create Account</button>
      </form>
    <?php endif; ?>

    <?php if ($message_type === "success"): ?>
      <a href="login.php" class="btn-outline" style="margin-top:15px; display:block; text-align:center;">
        Back to Login
      </a>
    <?php endif; ?>

  </div>
</div>
</div>
>>>>>>> 1725bfd (added admin dashboard and create teacher dashboard)

</body>
</html>
