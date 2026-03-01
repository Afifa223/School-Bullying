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
    $email_value = $email;

    if ($email === "" || $password === "") {

        $message = "All fields are required.";
        $message_type = "error";
    } else {

        // ================= ADMIN LOGIN =================
        $stmt = $conn->prepare("SELECT admin_id, full_name, email, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin["password"])) {
            $_SESSION["role"] = "admin";
            $_SESSION["user"] = [
                "id" => (int)$admin["admin_id"],
                "name" => $admin["full_name"],
                "email" => $admin["email"]
            ];
            header("Location: admin_dashboard.php");
            exit;

        }

        // ================= TEACHER LOGIN =================
        $stmt = $conn->prepare("SELECT teacher_id, email, password FROM teachers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $teacher = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($teacher && password_verify($password, $teacher["password"])) {
            $_SESSION["role"] = "teacher";
            $_SESSION["user"] = [
                "id" => (int)$teacher["teacher_id"],
                "name" => $teacher["email"], // no name column in teachers table
                "email" => $teacher["email"]
            ];
            header("Location: teacher_dashboard.php");
            exit;
        }

        // ================= STUDENT LOGIN =================
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($student && password_verify($password, $student["password"])) {
            $_SESSION["role"] = "student";
            $_SESSION["user"] = [
                "id" => (int)$student["student_id"],
                "name" => trim($student["first_name"] . " " . $student["last_name"]),
                "email" => $student["email"]
            ];
            header("Location: student_dashboard.php");
            exit;
        }

        $message = "Invalid email or password!";
        $message_type = "error";
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
      <div class="alert <?php echo htmlspecialchars($message_type); ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email_value); ?>" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button class="btn" type="submit">Login</button>
    </form>

    <div class="divider"><span>OR</span></div>
    <a class="btn-outline" href="register.php">Create a new student account</a>

  </div>
</div>
</div>


</body>
</html>
