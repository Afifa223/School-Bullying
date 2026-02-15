<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";

$email_value = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $email_value = $email; // Keep email after submit

    if ($email === "" || $password === "") {
    }
  }
$identifier_value = "";

// Student email rule: 7 digits + @gmail.com
function is_student_email(string $value): bool {
    return preg_match('/^[0-9]{7}@gmail\.com$/', $value) === 1;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $identifier = trim($_POST["identifier"] ?? "");
    $password   = $_POST["password"] ?? "";

    $identifier_value = $identifier;

    if ($identifier === "" || $password === "") {

        $message = "All fields are required.";
        $message_type = "error";
    } else {
        try {
            // Student login
            if (is_student_email($identifier)) {

                $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM student WHERE email = ?");
                $stmt->bind_param("s", $identifier);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($password, $user["password"])) {
                    session_regenerate_id(true);

                    // New unified session
                    $_SESSION["user"] = [
                        "role" => "student",
                        "id"   => $user["id"],
                        "name" => $user["first_name"] . " " . $user["last_name"],
                        "identifier" => $user["email"],
                    ];

                    // legacy (optional)
                    $_SESSION["student_id"] = $user["id"];
                    $_SESSION["student_name"] = $user["first_name"];

                    header("Location: student_home.php");
                    exit;
                } else {
                    $message = "Invalid student email or password.";
                    $message_type = "error";
                }
            } 
            // Teacher login
            else {
                $stmt = $conn->prepare("SELECT id, teacher_id, full_name, password FROM teachers WHERE teacher_id = ?");
                $stmt->bind_param("s", $identifier);
                $stmt->execute();
                $result = $stmt->get_result();
                $teacher = $result->fetch_assoc();
                $stmt->close();

                if ($teacher && password_verify($password, $teacher["password"])) {
                    session_regenerate_id(true);

                    $_SESSION["user"] = [
                        "role" => "teacher",
                        "id"   => $teacher["id"],
                        "name" => $teacher["full_name"],
                        "identifier" => $teacher["teacher_id"],
                    ];

                    header("Location: teacher_home.php");
                    exit;
                } else {
                    $message = "Invalid teacher ID or password.";
                    $message_type = "error";
                }
            }

        } catch (Exception $e) {
            $message = "Login error: " . $e->getMessage();
            $message_type = "error";
        }

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>SBMS | Login</title>

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Your existing theme -->
  <link rel="stylesheet" href="css/login.css?v=2" />
</head>
<body>

  <div class="page">
    <header class="page-header">
      <h1 class="system-title">School Bullying Management System</h1>
      <h2 class="page-title">Login</h2>
    </header>

    <div class="container">
      <div class="card">

        <?php if ($message): ?>
          <div class="alert <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <label>Student Email / Teacher ID</label>
          <input
            type="text"
            name="identifier"
            value="<?php echo htmlspecialchars($identifier_value); ?>"
            placeholder="Student: 2023001@gmail.com | Teacher: TCH-001"
            required
          />

          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required />

          <button class="btn" type="submit">Login</button>
        </form>

        <div class="divider"><span>OR</span></div>

        <!-- ONLY ONE register button -->
        <a class="btn-outline" href="register.php">Create a new student account</a>

      </div>
    </div>
  </div>

</body>
</html>
