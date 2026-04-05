<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$messageType = "";

/* Initialize variables */
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($full_name === "" || $email === "" || $password === "") {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        $check = $conn->prepare("SELECT teacher_id FROM teachers WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email already exists.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO teachers (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $hashedPassword);
            $stmt->execute();

            $message = "Teacher account created successfully.";
            $messageType = "success";

            $full_name = "";
            $email = "";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Teacher | SBMS</title>

<link rel="stylesheet" href="css/admin.css">
<link rel="stylesheet" href="css/add_teacher.css">
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
            <a href="admin_teachers_list.php">Teachers</a>
            <a class="btn outline" href="logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="hero">
    <div class="container">

        <div class="card add-teacher-card">
            <div class="form-head">
                <div>
                    <h2>Add New Teacher</h2>
                    <p class="muted">Create a new teacher account with a temporary password.</p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="teacher-form" autocomplete="off">
                <input type="text" name="fake_username" autocomplete="username" style="display:none">
                <input type="password" name="fake_password" autocomplete="new-password" style="display:none">

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        placeholder="Enter teacher full name"
                        value="<?php echo htmlspecialchars($full_name); ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="teacher_email">Email Address</label>
                    <input
                        type="email"
                        id="teacher_email"
                        name="email"
                        placeholder="Enter teacher email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="teacher_password">Temporary Password</label>
                    <div class="password-wrap">
                        <input
                            type="password"
                            id="teacher_password"
                            name="password"
                            placeholder="Enter temporary password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-btn" onclick="togglePassword()">Show</button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-btn">Create Teacher</button>
                    <button type="reset" class="secondary-btn" onclick="clearTeacherForm()">Reset</button>
                </div>
            </form>
        </div>

        <div class="footer">Admin tools · Add Teacher</div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById("teacher_password");
    const btn = document.querySelector(".toggle-btn");

    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "Hide";
    } else {
        input.type = "password";
        btn.textContent = "Show";
    }
}

function clearTeacherForm() {
    setTimeout(() => {
        document.getElementById("full_name").value = "";
        document.getElementById("teacher_email").value = "";
        document.getElementById("teacher_password").value = "";
    }, 10);
}

window.addEventListener("load", function () {
    const email = document.getElementById("teacher_email");
    const password = document.getElementById("teacher_password");

    password.value = "";

    if (performance.navigation && performance.navigation.type === 1) {
        password.value = "";
    }

    setTimeout(() => {
        password.value = "";
    }, 100);
});
</script>

</body>
</html>