<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Check if resource ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $resource_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // ✅ Prepare SQL to delete resource only if it belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM resources WHERE resource_id = ? AND added_by = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $resource_id, $user_id);

    if ($stmt->execute()) {
        // ✅ Success: redirect with message
        $stmt->close();
        $conn->close();
        header("Location: user_dashboard.php?msg=deleted");
        exit();
    } else {
        // ❌ SQL execution error
        $stmt->close();
        $conn->close();
        die("Failed to delete resource: " . $stmt->error);
    }

} else {
    // ❌ Invalid resource ID
    die("Invalid request.");
}
?>
