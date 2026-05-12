<?php

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Please login to save this order.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$items = $payload['items'] ?? [];

if (!is_array($items) || count($items) === 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Your cart is empty.']);
    exit;
}

$total = 0;

foreach ($items as $item) {
    $price = (float)($item['price'] ?? 0);
    $quantity = (int)($item['quantity'] ?? 0);

    if ($price < 0 || $quantity < 1) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Invalid order item.']);
        exit;
    }

    $total += $price * $quantity;
}

$userId = current_user_id();
$conn->begin_transaction();

try {
    $orderStmt = $conn->prepare('INSERT INTO orders (user_id, total_amount) VALUES (?, ?)');
    $orderStmt->bind_param('id', $userId, $total);
    $orderStmt->execute();
    $orderId = $conn->insert_id;
    $orderStmt->close();

    $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, item_name, category, price, quantity) VALUES (?, ?, ?, ?, ?)');

    foreach ($items as $item) {
        $name = trim((string)($item['name'] ?? 'Menu item'));
        $category = trim((string)($item['category'] ?? 'Menu'));
        $price = (float)$item['price'];
        $quantity = (int)$item['quantity'];
        $itemStmt->bind_param('issdi', $orderId, $name, $category, $price, $quantity);
        $itemStmt->execute();
    }

    $itemStmt->close();

    $source = 'order';
    $visitStmt = $conn->prepare('INSERT INTO user_visits (user_id, visited_at, source) VALUES (?, NOW(), ?)');
    $visitStmt->bind_param('is', $userId, $source);
    $visitStmt->execute();
    $visitStmt->close();

    $conn->commit();
    echo json_encode(['ok' => true, 'order_id' => $orderId]);
} catch (Throwable $error) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Order could not be saved.']);
}
?>
