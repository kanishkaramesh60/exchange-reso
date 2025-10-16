<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $user_id = $_SESSION['user_id'];

    // Insert resource into database
    $stmt = $conn->prepare("INSERT INTO resources (user_id, title, description, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $category);

    if ($stmt->execute()) {
        $success = "Resource submitted successfully! Waiting for admin approval.";
    } else {
        $error = "Error submitting resource: " . $stmt->error;
    }

    $stmt->close();
}
?>

<h2>Add New Resource</h2>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required></textarea><br><br>

    <label>Category:</label><br>
    <input type="text" name="category" required><br><br>

    <button type="submit">Submit Resource</button>
</form>
<br>
<a href="user_dashboard.php">Back to Dashboard</a>
