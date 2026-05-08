<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        echo "Login attempt for: ". htmlspecialchars($email);
    } else {
        echo "Please fill in all fields";
    }
}
?>