<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in admins
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Get the resource ID from URL
if (isset($_GET['id'])) {
    $resource_id = $_GET['id'];

    // ✅ Update status to 'rejected'
    $stmt = $conn->prepare("UPDATE resources SET status = 'rejected' WHERE resource_id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();

    // ✅ Close statement and connection (best practice)
    $stmt->close();
    $conn->close();

    // ✅ Optional: add success message (uncomment if needed)
    // $_SESSION['message'] = "Resource rejected successfully!";

    // ✅ Redirect back to dashboard
    header("Location: admin_dashboard.php");
    exit();
} else {
    echo "Invalid request.";
}
?>
