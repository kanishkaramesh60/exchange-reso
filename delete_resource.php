<?php
session_start();
include 'db.php';

// Only admin can access
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if resource ID is provided
if (!empty($_GET['id'])) {
    $resource_id = intval($_GET['id']);

    // Fetch the image path first
    $stmt = $conn->prepare("SELECT image FROM resources WHERE resource_id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();

    // Delete image file from server if it exists
    if (!empty($image_path) && file_exists($image_path)) {
        unlink($image_path);
    }

    // Delete resource from database
    $stmt = $conn->prepare("DELETE FROM resources WHERE resource_id = ?");
    $stmt->bind_param("i", $resource_id);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: view_resource.php?msg=deleted");
        exit();
    } else {
        die("Error deleting resource: " . $stmt->error);
    }

} else {
    echo "Invalid request. No resource ID provided.";
}
?>
