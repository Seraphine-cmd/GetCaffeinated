<?php

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/EditProfile.php');
    exit;
}

$userId = current_user_id();
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '') {
    redirect_with_status('../Pages/EditProfile.php', 'invalid');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('../Pages/EditProfile.php', 'invalid_email');
}

if ($password !== '' && strlen($password) < 8) {
    redirect_with_status('../Pages/EditProfile.php', 'password_short');
}

if ($password !== $confirmPassword) {
    redirect_with_status('../Pages/EditProfile.php', 'password_mismatch');
}

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
$checkStmt->bind_param('si', $email, $userId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    redirect_with_status('../Pages/EditProfile.php', 'email_exists');
}

$checkStmt->close();

if ($password !== '') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
    if (!$stmt) {
        redirect_with_status('../Pages/EditProfile.php', 'error');
    }
    $stmt->bind_param('sssi', $name, $email, $hashedPassword, $userId);
} else {
    $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    if (!$stmt) {
        redirect_with_status('../Pages/EditProfile.php', 'error');
    }
    $stmt->bind_param('ssi', $name, $email, $userId);
}

if (!$stmt->execute()) {
    redirect_with_status('../Pages/EditProfile.php', 'error');
}

$_SESSION['user_name'] = $name;
redirect_with_status('../Pages/EditProfile.php', 'profile_updated');
?>
