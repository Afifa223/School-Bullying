<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";
$show_success_dialog = false;

$values = [
  "first_name" => "",
  "last_name"  => "",
  "email"      => ""
];

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
    $message = "Email must be 7 digits followed by @gmail.com (example: 2023001@gmail.com).";
    $message_type = "error";
  }
  elseif ($password !== $confirm) {
    $message = "Passwords do not match.";
    $message_type = "error";
  }
  elseif (!valid_password($password)) {
    $message = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
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
      $message = "Roll number must be between 001 and 100.";
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

        $message = "Registration successful!";
        $message_type = "success";
        $show_success_dialog = true;

        $values = [
          "first_name" => "",
          "last_name"  => "",
          "email"      => ""
        ];

      } catch (mysqli_sql_exception $e) {
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>SBMS | Student Registration</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="css/login.css?v=21" />
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

      <?php if ($message && $message_type === "error"): ?>
        <div class="alert <?php echo htmlspecialchars($message_type); ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <label>First Name</label>
        <input
          type="text"
          name="first_name"
          value="<?php echo htmlspecialchars($values["first_name"]); ?>"
          placeholder="Enter your first name"
          required
        >

        <label>Last Name</label>
        <input
          type="text"
          name="last_name"
          value="<?php echo htmlspecialchars($values["last_name"]); ?>"
          placeholder="Enter your last name"
          required
        >

        <label>Email</label>
        <input
          type="text"
          name="email"
          value="<?php echo htmlspecialchars($values["email"]); ?>"
          placeholder="2023001@gmail.com"
          required
        >

       <label>Password</label>
<div class="password-field">
  <input
    type="password"
    name="password"
    id="regPassword"
    placeholder="Create your password"
    required
  >

  <span class="toggle-password" onclick="togglePassword('regPassword', this)">
    <svg viewBox="0 0 24 24" class="eye-icon">
      <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
    </svg>
  </span>
</div>

<label>Confirm Password</label>
<div class="password-field">
  <input
    type="password"
    name="confirm_password"
    id="confirmPassword"
    placeholder="Confirm your password"
    required
  >

  <span class="toggle-password" onclick="togglePassword('confirmPassword', this)">
    <svg viewBox="0 0 24 24" class="eye-icon">
      <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
    </svg>
  </span>
</div>
        <button class="btn" type="submit">Create Account</button>
      </form>
    </div>
  </div>
</div>

<?php if ($show_success_dialog): ?>
  <div class="dialog-backdrop" id="successDialog">
    <div class="dialog-box">
      <div class="dialog-icon">✓</div>
      <h3>Success</h3>
      <p><?php echo htmlspecialchars($message); ?></p>
      <div class="dialog-note">Redirecting to login page...</div>
    </div>
  </div>

  <script>
    window.addEventListener("load", function () {
      var dialog = document.getElementById("successDialog");
      if (dialog) {
        dialog.style.display = "flex";
        setTimeout(function () {
          window.location.href = "login.php";
        }, 2000);
      }
    });
  </script>
<?php endif; ?>
<script>
function togglePassword(id, el) {
  const input = document.getElementById(id);
  const svg = el.querySelector("svg");

  if (input.type === "password") {
    input.type = "text";

    // eye-off icon
    svg.innerHTML = `
      <path d="M17.94 17.94A10.92 10.92 0 0 1 12 19c-7 0-10-7-10-7a21.77 21.77 0 0 1 5.06-6.94M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 10 7 10 7a21.77 21.77 0 0 1-3.06 4.28M1 1l22 22"/>
    `;
  } else {
    input.type = "password";

    // normal eye
    svg.innerHTML = `
      <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
    `;
  }
}
</script>
</body>
</html>