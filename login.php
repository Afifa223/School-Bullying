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
        $message = "All fields are required.";
        $message_type = "error";
    } else {

        $stmt = $conn->prepare("SELECT * FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["student_id"] = $user["id"];
            $_SESSION["student_name"] = $user["first_name"];

            $message = "✅ Login Successful! Welcome " . htmlspecialchars($user["first_name"]);
            $message_type = "success";
        } else {
            $message = "❌ Invalid email or password!";
            $message_type = "error";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>SBMS | Login</title>
<link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="page">
<header class="page-header">
<h1 class="system-title">School Bullying Management System</h1>
<h2 class="page-title">Student Login</h2>
</header>

<div class="container">
<div class="card">

<?php if ($message): ?>
<div class="alert <?php echo $message_type; ?>">
<?php echo $message; ?>
</div>
<?php endif; ?>

<form method="POST">

<label>Email</label>
<input type="email" name="email" 
value="<?php echo htmlspecialchars($email_value); ?>" required>

<label>Password</label>
<input type="password" name="password" required>

<button class="btn" type="submit">Login</button>

</form>

<div class="divider"><span>OR</span></div>

<a class="btn-outline" href="register.php">Create a new account</a>

</div>
</div>
</div>

</body>
</html>
