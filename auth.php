<?php
// auth.php
session_start();

function require_login(): void {
    if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["role"])) {
        header("Location: login.php");
        exit;
    }
}

function require_role(string $role): void {
    require_login();

    if ($_SESSION["user"]["role"] !== $role) {
        // If logged in but wrong role, redirect correctly
        if ($_SESSION["user"]["role"] === "student") {
            header("Location: student_home.php");
        } elseif ($_SESSION["user"]["role"] === "teacher") {
            header("Location: teacher_home.php");
        } else {
            header("Location: login.php");
        }
        exit;
    }
}
