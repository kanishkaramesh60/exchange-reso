<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid resource ID.");
}

$resource_id = intval($_GET['id']);

// Check if already booked
$stmt = $conn->prepare("SELECT * FROM bookings WHERE resource_id=? AND user_id=?");
$stmt->bind_param("ii", $resource_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $_SESSION['message'] = "You have already booked this resource.";
    header("Location: user_dashboard.php");
    exit();
}

// Insert booking
$stmt = $conn->prepare("INSERT INTO bookings (resource_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $resource_id, $user_id);

if($stmt->execute()){
    $_SESSION['message'] = "Resource booked successfully!";
    header("Location: user_dashboard.php");
} else {
    die("Error booking resource: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
