<?php
require __DIR__ . '/../php/db.php';
require __DIR__ . '/../php/auth.php';

require_login();

$userId = current_user_id();
$stmt = $conn->prepare('SELECT name, email, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: ../php/logout.php');
    exit;
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
    <title>Get Caffeinated | Edit Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/styles/Page.css">
    <link rel="stylesheet" href="../Assets/styles/ProfileMenu.css">
    <link rel="stylesheet" href="../Assets/styles/Profile.css">
    <script src="../Assets/scripts/AuthStatus.js" defer></script>
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
        <header class="profile-hero compact-profile-hero">
            <div>
                <p class="eyebrow">Profile settings</p>
                <h2>Modify your account.</h2>
                <p>Update your display name, email address, and password settings.</p>
            </div>
            <aside class="profile-card">
                <span>Current account</span>
                <strong><?= e($user['email']); ?></strong>
                <p>Member since <?= e(date('M d, Y', strtotime($user['created_at']))); ?></p>
            </aside>
        </header>

        <section class="settings-panel" id="settings">
            <form class="settings-form" action="../php/update_profile.php" method="post">
                <p class="eyebrow">Account details</p>
                <h3>Personal information</h3>
                <p class="form-status" data-auth-status hidden></p>

                <label for="name">Full name</label>
                <input id="name" name="name" type="text" maxlength="120" value="<?= e($user['name']); ?>" required>

                <label for="email">Email address</label>
                <input id="email" name="email" type="email" maxlength="160" value="<?= e($user['email']); ?>" required>

                <label for="password">New password <span>(optional)</span></label>
                <input id="password" name="password" type="password" minlength="8" placeholder="Leave blank to keep current password">

                <label for="confirm_password">Confirm new password</label>
                <input id="confirm_password" name="confirm_password" type="password" minlength="8" placeholder="Repeat new password">

                <button type="submit">Save changes</button>
            </form>

            <aside class="settings-card">
                <span>Settings included</span>
                <h3>Account controls</h3>
                <p>Manage profile identity, update login credentials, review dashboard activity, and safely logout from the account menu.</p>
                <a href="../Pages/Profile.php">Back to dashboard</a>
            </aside>
        </section>
    </main>
</body>
</html>
