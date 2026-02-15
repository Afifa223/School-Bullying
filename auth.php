<?php
session_start();

function require_login(): void {
    if (!isset($_SESSION["role"]) || !isset($_SESSION["user"])) {
        header("Location: login.php");
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    if (($_SESSION["role"] ?? "") !== $role) {
        header("Location: login.php");
        exit;
    }
}

function current_user_name(): string {
    $name = $_SESSION["user"]["name"] ?? "User";
    return htmlspecialchars($name, ENT_QUOTES, "UTF-8");
}
