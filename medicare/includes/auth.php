
<?php
// includes/auth.php
session_start();

function require_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
}

function require_role($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header("Location: index.php");
        exit();
    }
}

function current_user_id() {
    return $_SESSION['user']['id'] ?? null;
}
?>
