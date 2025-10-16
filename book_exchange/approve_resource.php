<?php
session_start();
include 'db.php';

// ✅ Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// ✅ Process approve/reject
if (isset($_GET['id']) && isset($_GET['action'])) {
    $resource_id = intval($_GET['id']);
    $action = $_GET['action'];

    if (in_array($action, ['approve', 'reject'])) {
        $stmt = $conn->prepare("UPDATE resources SET status=? WHERE resource_id=?");
        $stmt->bind_param("si", $action, $resource_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Resource has been " . ucfirst($action) . "d successfully.";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// ✅ Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
