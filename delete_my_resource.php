<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $resource_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Delete only if this user owns the resource
    $stmt = $conn->prepare("DELETE FROM resources WHERE resource_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resource_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    header("Location: user_dashboard.php?msg=deleted");
    exit();
} else {
    echo "Invalid request.";
}
?>
