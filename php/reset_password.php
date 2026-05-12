<?php

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/ForgotPassword.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($email === '' || $password === '' || $confirmPassword === '') {
    redirect_with_status('../Pages/ForgotPassword.html', 'invalid');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('../Pages/ForgotPassword.html', 'invalid_email');
}

if (strlen($password) < 8) {
    redirect_with_status('../Pages/ForgotPassword.html', 'password_short');
}

if ($password !== $confirmPassword) {
    redirect_with_status('../Pages/ForgotPassword.html', 'password_mismatch');
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');

if (!$stmt) {
    redirect_with_status('../Pages/ForgotPassword.html', 'error');
}

$stmt->bind_param('ss', $hashedPassword, $email);
$stmt->execute();

if ($stmt->affected_rows < 1) {
    redirect_with_status('../Pages/ForgotPassword.html', 'account_missing');
}

redirect_with_status('../Pages/LoginPage.html', 'reset_success');
?>
