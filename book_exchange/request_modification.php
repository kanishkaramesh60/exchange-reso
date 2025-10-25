<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate resource_id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid request.";
    header("Location: user_dashboard.php");
    exit();
}

$resource_id = intval($_GET['id']);

// Check if resource exists and belongs to the user
$stmt = $conn->prepare("SELECT status FROM resources WHERE resource_id=? AND added_by=?");
$stmt->bind_param("ii", $resource_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Invalid resource or not allowed.";
    header("Location: user_dashboard.php");
    exit();
}

$row = $result->fetch_assoc();

// Only allow request if resource is approved
if ($row['status'] !== 'approved') {
    $_SESSION['message'] = "Modification request can only be sent for approved resources.";
    header("Location: user_dashboard.php");
    exit();
}

// Check if a pending request already exists
$stmt_check = $conn->prepare("SELECT id FROM modification_requests WHERE resource_id=? AND user_id=? AND status='pending'");
$stmt_check->bind_param("ii", $resource_id, $user_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $_SESSION['message'] = "You already have a pending modification request for this resource.";
    $stmt_check->close();
    $conn->close();
    header("Location: user_dashboard.php");
    exit();
}
$stmt_check->close();

// Insert modification request
$stmt_insert = $conn->prepare("INSERT INTO modification_requests (resource_id, user_id) VALUES (?, ?)");
$stmt_insert->bind_param("ii", $resource_id, $user_id);
if ($stmt_insert->execute()) {
    $_SESSION['message'] = "Modification request sent to admin successfully.";
} else {
    $_SESSION['message'] = "Failed to send request. Try again.";
}
$stmt_insert->close();
$conn->close();

header("Location: user_dashboard.php");
exit();
?>
