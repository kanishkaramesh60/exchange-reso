<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$resource_id = $_GET['resource_id'] ?? 0;

if ($resource_id) {
    // Check if already favorite
    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id=? AND resource_id=?");
    $check->bind_param("ii", $user_id, $resource_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Remove from favorites
        $del = $conn->prepare("DELETE FROM favorites WHERE user_id=? AND resource_id=?");
        $del->bind_param("ii", $user_id, $resource_id);
        $del->execute();
        $del->close();
    } else {
        // Add to favorites
        $add = $conn->prepare("INSERT INTO favorites (user_id, resource_id) VALUES (?, ?)");
        $add->bind_param("ii", $user_id, $resource_id);
        $add->execute();
        $add->close();
    }
    $check->close();
}

$conn->close();

// Redirect back to the resources page
header("Location: all_resources.php");
exit();
?>
