<?php

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/Review.html');
    exit;
}

$name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['customer_email'] ?? '');
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0, 'max_range' => 5]
]);
$feedback = trim($_POST['feedback'] ?? '');

if ($name === '' || $rating === false || $rating === null || $feedback === '') {
    header('Location: ../Pages/Review.html?status=invalid');
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../Pages/Review.html?status=invalid_email');
    exit;
}

$stmt = $conn->prepare('INSERT INTO reviews (customer_name, customer_email, rating, feedback) VALUES (?, ?, ?, ?)');

if (!$stmt) {
    header('Location: ../Pages/Review.html?status=error');
    exit;
}

$stmt->bind_param('ssis', $name, $email, $rating, $feedback);

if ($stmt->execute()) {
    header('Location: ../Pages/Review.html?status=success');
} else {
    header('Location: ../Pages/Review.html?status=error');
}

$stmt->close();
$conn->close();
exit;
?>
