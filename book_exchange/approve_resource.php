<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $resource_id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE resources SET status = 'approved' WHERE resource_id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to dashboard
    header("Location: admin_dashboard.php");
    exit();
} else {
    echo "Invalid request.";
}
?>
