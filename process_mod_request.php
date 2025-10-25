<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$id = intval($_GET['id']);
$action = $_GET['action'] ?? '';

if(!in_array($action,['approve','reject'])){
    die("Invalid request.");
}

// Update request status
$stmt = $conn->prepare("UPDATE modification_requests SET status=? WHERE id=?");
$stmt->bind_param("si", $action, $id);
$stmt->execute();
$stmt->close();

// Optional: if approved, set resource status back to 'pending' so user can edit
if($action=='approve'){
    $stmt2 = $conn->prepare("UPDATE resources r JOIN modification_requests mr ON r.resource_id=mr.resource_id SET r.status='pending' WHERE mr.id=?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();
}

$_SESSION['message'] = "Modification request $action successfully!";
header("Location: modification_requests.php");
exit();
