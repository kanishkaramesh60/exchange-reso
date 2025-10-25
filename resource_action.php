<?php
session_start();
include 'db.php';

// ✅ Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// ✅ Process approve/reject
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if (in_array($action, ['approve','reject'])) {
        // Map action to correct status value
        $status = ($action === 'approve') ? 'approved' : 'rejected';

        $stmt = $conn->prepare("UPDATE resources SET status=? WHERE resource_id=?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Resource has been $status successfully.";
        } else {
            $_SESSION['message'] = "Error: ".$conn->error;
        }
    }
}

// ✅ Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
