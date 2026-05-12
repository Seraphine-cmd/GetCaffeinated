<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../Pages/LoginPage.html?status=login_required');
        exit;
    }
}

function current_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function redirect_with_status(string $path, string $status): void
{
    header('Location: ' . $path . '?status=' . urlencode($status));
    exit;
}
?>
