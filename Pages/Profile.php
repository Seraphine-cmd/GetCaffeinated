<?php
require __DIR__ . '/../php/db.php';
require __DIR__ . '/../php/auth.php';

require_login();

$userId = current_user_id();
$userStmt = $conn->prepare('SELECT name, email, created_at FROM users WHERE id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) {
    header('Location: ../php/logout.php');
    exit;
}

$profileVisitSource = 'profile';
$profileVisitStmt = $conn->prepare('INSERT INTO user_visits (user_id, visited_at, source) VALUES (?, NOW(), ?)');

if ($profileVisitStmt) {
    $profileVisitStmt->bind_param('is', $userId, $profileVisitSource);
    $profileVisitStmt->execute();
    $profileVisitStmt->close();
}

$statsStmt = $conn->prepare('SELECT COUNT(*) AS visits FROM user_visits WHERE user_id = ?');
$statsStmt->bind_param('i', $userId);
$statsStmt->execute();
$visitCount = (int)$statsStmt->get_result()->fetch_assoc()['visits'];
$statsStmt->close();

$orderStatsStmt = $conn->prepare('SELECT COUNT(*) AS orders, COALESCE(SUM(total_amount), 0) AS total_spent FROM orders WHERE user_id = ?');
$orderStatsStmt->bind_param('i', $userId);
$orderStatsStmt->execute();
$orderStats = $orderStatsStmt->get_result()->fetch_assoc();
$orderStatsStmt->close();

$recentOrdersStmt = $conn->prepare('SELECT id, total_amount, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$recentOrdersStmt->bind_param('i', $userId);
$recentOrdersStmt->execute();
$recentOrders = $recentOrdersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentOrdersStmt->close();

$activityStmt = $conn->prepare('SELECT DATE(visited_at) AS activity_date, COUNT(*) AS total FROM user_visits WHERE user_id = ? AND visited_at >= DATE_SUB(CURDATE(), INTERVAL 364 DAY) GROUP BY DATE(visited_at)');
$activityStmt->bind_param('i', $userId);
$activityStmt->execute();
$activityRows = $activityStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$activityStmt->close();

$activity = [];

foreach ($activityRows as $row) {
    $activity[$row['activity_date']] = (int)$row['total'];
}

$start = new DateTimeImmutable('monday this week -51 weeks');
$today = new DateTimeImmutable('today');
$monthLabels = [];
$cells = [];

for ($week = 0; $week < 52; $week++) {
    $weekStart = $start->modify('+' . $week . ' weeks');
    $month = $weekStart->format('M');

    if ($week === 0 || $month !== $start->modify('+' . ($week - 1) . ' weeks')->format('M')) {
        $monthLabels[$week] = $month;
    }

    for ($day = 0; $day < 7; $day++) {
        $date = $weekStart->modify('+' . $day . ' days');
        $key = $date->format('Y-m-d');
        $count = $date > $today ? 0 : ($activity[$key] ?? 0);
        $level = min(4, $count);
        $cells[] = ['date' => $key, 'count' => $count, 'level' => $level, 'future' => $date > $today];
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Caffeinated | Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/styles/Page.css">
    <link rel="stylesheet" href="../Assets/styles/ProfileMenu.css">
    <link rel="stylesheet" href="../Assets/styles/Profile.css">
</head>
<body>
    <nav class="navbar">
        <a href="../Pages/Home.html" class="logo-container" aria-label="Get Caffeinated home">
            <img src="../Assets/Images/Logo.png" class="logo" alt="Get Caffeinated logo">
            <div class="logo-text">
                <h1>Get Caffeinated</h1>
                <p>Coffee. Focus. Repeat.</p>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="../Pages/Home.html">Home</a></li>
            <li><a href="../Pages/Menu.html">Menu</a></li>
            <li><a href="../Pages/About.html">About</a></li>
            <li><a href="../Pages/Contact.html">Contact</a></li>
            <li><a href="../Pages/Review.html">Reviews</a></li>
        </ul>

        <a class="btn-order" href="../Pages/Menu.html">Order now</a>
        <a class="btn-cart" href="../Pages/Cart.html">View cart</a>

        <details class="profile-menu">
            <summary aria-label="Open profile menu"><span><?= strtoupper(substr(e($user['name']), 0, 1)); ?></span></summary>
            <div class="profile-popover">
                <p class="profile-kicker">Account</p>
                <a href="../Pages/Profile.php">Dashboard</a>
                <a href="../Pages/EditProfile.php">Modify profile</a>
                <a href="../Pages/Profile.php#orders">Orders & visits</a>
                <a href="../Pages/EditProfile.php#settings">Settings</a>
                <a class="danger-link" href="../php/logout.php">Logout</a>
            </div>
        </details>
    </nav>

    <main class="profile-page">
        <header class="profile-hero">
            <div>
                <p class="eyebrow">Member profile</p>
                <h2>Hi, <?= e($user['name']); ?>.</h2>
                <p>Track your visits, saved orders, and cafe activity in one place.</p>
            </div>
            <aside class="profile-card">
                <span>Account</span>
                <strong><?= e($user['email']); ?></strong>
                <p>Member since <?= e(date('M d, Y', strtotime($user['created_at']))); ?></p>
            </aside>
        </header>

        <section class="stat-grid" aria-label="Profile stats">
            <article>
                <span>Total visits</span>
                <strong><?= $visitCount; ?></strong>
                <p>Login, profile, and order activity.</p>
            </article>
            <article>
                <span>Orders saved</span>
                <strong><?= (int)$orderStats['orders']; ?></strong>
                <p>Completed from your cart.</p>
            </article>
            <article>
                <span>Total spent</span>
                <strong><?= number_format((float)$orderStats['total_spent'], 2); ?></strong>
                <p>Recorded checkout value.</p>
            </article>
        </section>

        <section class="activity-card" aria-label="Visit activity calendar">
            <div class="activity-heading">
                <div>
                    <p class="eyebrow">Visit tracker</p>
                    <h3>Your cafe activity</h3>
                </div>
                <p><?= array_sum($activity); ?> visits in the last year</p>
            </div>

            <div class="activity-calendar">
                <div class="month-row">
                    <?php for ($week = 0; $week < 52; $week++): ?>
                        <span><?= e($monthLabels[$week] ?? ''); ?></span>
                    <?php endfor; ?>
                </div>
                <div class="calendar-body">
                    <div class="day-labels" aria-hidden="true">
                        <span>Mon</span>
                        <span></span>
                        <span>Wed</span>
                        <span></span>
                        <span>Fri</span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="calendar-grid">
                        <?php foreach ($cells as $cell): ?>
                            <span class="activity-cell level-<?= $cell['future'] ? 0 : $cell['level']; ?>" title="<?= e($cell['date']); ?>: <?= $cell['count']; ?> visits"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="activity-legend">
                    <span>Less</span>
                    <i class="level-0"></i>
                    <i class="level-1"></i>
                    <i class="level-2"></i>
                    <i class="level-3"></i>
                    <i class="level-4"></i>
                    <span>More</span>
                </div>
            </div>
        </section>

        <section class="orders-panel" id="orders">
            <div>
                <p class="eyebrow">Recent orders</p>
                <h3>Saved checkout history</h3>
            </div>
            <div class="order-list">
                <?php if (count($recentOrders) === 0): ?>
                    <article class="empty-order">No saved orders yet. Add items from the menu and checkout while logged in.</article>
                <?php endif; ?>

                <?php foreach ($recentOrders as $order): ?>
                    <article class="order-row">
                        <div>
                            <span>Order #<?= (int)$order['id']; ?></span>
                            <strong><?= e(date('M d, Y h:i A', strtotime($order['created_at']))); ?></strong>
                        </div>
                        <p><?= number_format((float)$order['total_amount'], 2); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
