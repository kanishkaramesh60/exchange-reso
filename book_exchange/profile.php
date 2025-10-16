<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current user details
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate password if entered
    $update_password = false;
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $message = "<p class='error'>❌ Passwords do not match.</p>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_password = true;
        }
    }

    if ($message === '') {
        if ($update_password) {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        }

        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Details updated successfully.</p>";
            $_SESSION['user_name'] = $name; // update session name
        } else {
            $message = "<p class='error'>❌ Failed to update details: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Book Exchange</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .container { width: 40%; margin: auto; background: #f5f5f5; padding: 20px; border-radius: 10px; }
        h2 { text-align: center; }
        form input { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .success { color: green; text-align: center; font-weight: bold; }
        .error { color: red; text-align: center; font-weight: bold; }
        nav { text-align: center; margin-bottom: 20px; }
        nav a { margin: 0 10px; color: #4CAF50; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>My Profile</h2>

    <nav>
        <a href="user_dashboard.php">Dashboard</a> |
        <a href="upload_resource.php">Add Resource</a> |
        <a href="all_resources.php">All Resources</a> |
        <a href="logout.php">Logout</a>
    </nav>

    <?= $message ?>

    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>">

        <label for="password">New Password (leave blank to keep current):</label>
        <input type="password" name="password" id="password">

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password">

        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>
