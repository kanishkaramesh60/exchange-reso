<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

// Check if user is logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: user_dashboard.php");
    exit();
}

// If no one is logged in, redirect to login page
header("Location: login.php");
exit();
?>
