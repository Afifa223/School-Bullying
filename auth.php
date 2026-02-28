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



function current_user_id(): int {
    return (int)($_SESSION["user"]["id"] ?? 0);
}

function current_user_email(): string {
    $email = $_SESSION["user"]["email"] ?? "";
    return htmlspecialchars($email, ENT_QUOTES, "UTF-8");
}

/* --- Simple CSRF --- */
function csrf_token(): string {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
    }
    return $_SESSION["csrf_token"];
}

function csrf_verify(): void {
    $token = $_POST["csrf_token"] ?? "";
    if (!$token || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
        http_response_code(403);
        die("Invalid CSRF token.");
    }
}