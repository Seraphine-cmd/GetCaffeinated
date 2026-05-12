<?php

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/LoginPage.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    redirect_with_status('../Pages/LoginPage.html', 'invalid');
}

$stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');

if (!$stmt) {
    redirect_with_status('../Pages/LoginPage.html', 'error');
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    redirect_with_status('../Pages/LoginPage.html', 'invalid_login');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_name'] = $user['name'];

$visitStmt = $conn->prepare('INSERT INTO user_visits (user_id, visited_at, source) VALUES (?, NOW(), ?)');
$source = 'login';
$userId = (int)$user['id'];

if ($visitStmt) {
    $visitStmt->bind_param('is', $userId, $source);
    $visitStmt->execute();
    $visitStmt->close();
}

header('Location: ../Pages/Profile.php');
exit;
?>
