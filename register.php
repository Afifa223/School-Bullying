<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require "db.php";

$message = "";
$message_type = "";

$email_value = "";
$first_value = "";
$last_value  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $first_name = trim($_POST["first_name"] ?? "");
    $last_name  = trim($_POST["last_name"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $password   = $_POST["password"] ?? "";
    $confirm    = $_POST["confirm_password"] ?? "";

    // Keep values after submit (nice UX)
    $email_value = $email;
    $first_value = $first_name;
    $last_value  = $last_name;

    if ($first_name === "" || $last_name === "" || $email === "" || $password === "" || $confirm === "") {
        $message = "All fields are required.";
        $message_type = "error";

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
    $message = "Student email must be 7 digits followed by @gmail.com (e.g. 2023009@gmail.com)";
    $message_type = "error";

    } else {

        // Extract year + roll from student email
        $username = substr($email, 0, 7);
        $year = (int) substr($username, 0, 4);
        $roll = (int) substr($username, 4, 3);

        if ($year < 2000 || $year > (int)date("Y")) {
            $message = "Invalid admission year in email.";
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

            if ($stmt) {
                $stmt->bind_param("ssiiss", $first_name, $last_name, $year, $roll, $email, $hashed_password);

               try {
    $stmt->execute();

    $message = "Registration successful!";
    $message_type = "success";

    // Clear fields after success
    $email_value = $first_value = $last_value = "";

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {
        // Duplicate email
        $message = "This email already exists. Please use another email.";
        $message_type = "error";
    } else {
        $message = "Something went wrong. Please try again.";
        $message_type = "error";
    }
}


    $stmt->close();
    } else {
       $message = "Database error: " . $conn->error;
         $message_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SBMS | Student Registration</title>
    <link rel="stylesheet" href="css/register.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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

        <p class="footer">© <?php echo date("Y"); ?> SBMS</p>
        </div>
    </div>
</div>


</body>
</html>
