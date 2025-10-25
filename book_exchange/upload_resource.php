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
$image_preview = '';

// Fetch user name safely from DB
$stmt = $conn->prepare("SELECT name FROM users WHERE id=?");
if (!$stmt) { die("Prepare failed: ".$conn->error); }
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $added_by = $user_id;

    // Handle image upload
    $image_path = NULL;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = basename($_FILES['image']['name']);
        $filename = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $filename);
        $target_file = $target_dir . time() . "_" . $filename;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_type, $allowed_types)) {
            $message = "<p class='error'>❌ Only JPG, JPEG, PNG, GIF files are allowed.</p>";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
                $image_preview = "<img src='" . htmlspecialchars($image_path) . "' width='150' style='margin-top:10px;' alt='Preview'>";
            } else {
                $message = "<p class='error'>❌ Failed to upload image.</p>";
            }
        }
    }

    // Insert into database
    if ($message === '') {
        $stmt = $conn->prepare("INSERT INTO resources (added_by, title, description, category, image, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        if (!$stmt) { die("Prepare failed: ".$conn->error); }
        $stmt->bind_param("issss", $added_by, $title, $description, $category, $image_path);
        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Resource added successfully! Waiting for admin approval.</p>";
            $image_preview = '';
        } else {
            $message = "<p class='error'>❌ Failed to add resource: " . $stmt->error . "</p>";
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
<title>Add Resource - Book Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #eef1f7;
    margin: 0;
    padding: 0;
}
header {
    color: #fff;
    padding: 15px;
    text-align: center;
}
nav a {
    color: #fff;
    text-decoration: none;
    margin: 0 10px;
    font-weight: bold;
}
nav a:hover { text-decoration: underline; }
.container {
    width: 50%;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #333;
}
input, select, textarea {
    width: 100%;
    padding: 8px;
    margin: 8px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
}
button {
    color: #fff;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
}
button:hover {
    background-color: #03a531ff;
}
.success {
    color: #5cb85c;
    text-align: center;
    font-weight: bold;
}
.error {
    color: #d9534f;
    text-align: center;
    font-weight: bold;
}
footer {
    text-align: center;
    padding: 10px;
    color: white;
    position: fixed;
    bottom: 0;
    width: 100%;
}
img {
    border-radius: 5px;
    border: 1px solid #ccc;
}
</style>
</head>
<body>
<header>
    <h1>📚 Resource Exchange</h1>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="all_resources.php">All Resources</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Add a New Resource</h2>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="4" required></textarea>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="">--Select Category--</option>
            <option value="Textbook">Textbook</option>
            <option value="Notes">Notes</option>
            <option value="Stationery">Stationery</option>
            <option value="Other">Other</option>
        </select>

        <label for="image">Upload Image (optional):</label>
        <input type="file" name="image" id="image" accept="image/*">
        <?= $image_preview ?>

        <br><br>
        <button type="submit">Upload Resource</button>
    </form>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Resource Exchange | Made with ❤️ by Students</p>
</footer>
</body>
</html>
