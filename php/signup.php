<?php

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/Signup.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
    redirect_with_status('../Pages/Signup.html', 'invalid');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('../Pages/Signup.html', 'invalid_email');
}

if (strlen($password) < 8) {
    redirect_with_status('../Pages/Signup.html', 'password_short');
}

if ($password !== $confirmPassword) {
    redirect_with_status('../Pages/Signup.html', 'password_mismatch');
}

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    redirect_with_status('../Pages/Signup.html', 'email_exists');
}

$checkStmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');

if (!$stmt) {
    redirect_with_status('../Pages/Signup.html', 'error');
}

$stmt->bind_param('sss', $name, $email, $hashedPassword);

if ($stmt->execute()) {
    redirect_with_status('../Pages/LoginPage.html', 'signup_success');
}

redirect_with_status('../Pages/Signup.html', 'error');
?>
