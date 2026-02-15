<?php
require "db.php";

$full_name = "Main Admin";
$email = "admin@school.com";
$password = "Admin@123";

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $full_name, $email, $hash);
$stmt->execute();

echo "Admin created!";
