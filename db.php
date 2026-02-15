<?php
$host = "127.0.0.1";
$dbname = "sbmsdb";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Optional: set charset (recommended)
$conn->set_charset("utf8mb4");
?>
