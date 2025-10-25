<?php
session_start();
include 'db.php';

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Check if resource ID and action are provided
if (isset($_GET['id'], $_GET['action'])) {
    $resource_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Map action to correct status
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        $status = 'pending';
    }

    $stmt = $conn->prepare("UPDATE resources SET status=? WHERE resource_id=?");
    $stmt->bind_param("si", $status, $resource_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Resource has been " . ucfirst($status) . ".";
}

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
