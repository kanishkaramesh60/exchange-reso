<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate input
if (!isset($_GET['booking_id'], $_GET['resource_id'])) {
    $_SESSION['message'] = "Invalid request.";
    header("Location: user_booking.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$resource_id = intval($_GET['resource_id']);

// ✅ Verify that the logged-in user owns this booking
$stmt = $conn->prepare("SELECT user_id FROM bookings WHERE booking_id=? AND resource_id=?");
if (!$stmt) {
    $_SESSION['message'] = "Database error: " . $conn->error;
    header("Location: user_booking.php");
    exit();
}
$stmt->bind_param("ii", $booking_id, $resource_id);
$stmt->execute();
$stmt->bind_result($booking_user_id);
$stmt->fetch();
$stmt->close();

if ($booking_user_id != $user_id) {
    $_SESSION['message'] = "You can only undo your own bookings.";
    header("Location: user_booking.php");
    exit();
}

// ✅ Use transaction to ensure consistency
$conn->begin_transaction();
try {
    // Delete booking
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id=?");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();

    // Update resource status back to approved
    $stmt = $conn->prepare("UPDATE resources SET status='approved' WHERE resource_id=?");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $_SESSION['message'] = "Booking undone successfully.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = "Error undoing booking: " . $e->getMessage();
}

// Redirect back to user booking page
header("Location: user_booking.php");
exit();
?>
