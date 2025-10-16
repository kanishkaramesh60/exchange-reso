<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$image_preview = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $user_id = $_SESSION['user_id'];

    // ✅ Handle image upload
    $image_path = NULL;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        // Sanitize file name
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

    // ✅ Insert into database if no error
    if ($message === '') {
        $stmt = $conn->prepare("INSERT INTO resources (user_id, title, description, category, image, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("issss", $user_id, $title, $description, $category, $image_path);

        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Resource added successfully! Waiting for admin approval.</p>";
            $image_preview = ''; // reset preview after successful upload
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
</head>
<body>
    <header>
        <h1>📚 Book Exchange</h1>
        <nav>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="upload_resource.php">Add Resource</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Add a New Resource</h2>

        <?= $message ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label for="title">Title:</label><br>
                <input type="text" name="title" id="title" required style="width: 30%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="description">Description:</label><br>
                <textarea name="description" id="description" rows="4" required style="width: 30%;"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="category">Category:</label><br>
                <select name="category" id="category" required style="width: 30%;">
                    <option value="">--Select Category--</option>
                    <option value="Textbook">Textbook</option>
                    <option value="Notes">Notes</option>
                    <option value="Stationery">Stationery</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="image">Upload Image (optional):</label><br>
                <input type="file" name="image" id="image" accept="image/*"><br>
                <?= $image_preview ?>
            </div>

            <div>
                <button type="submit" style="padding: 10px 20px;">Upload Resource</button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; <?= date('Y'); ?> Book Exchange | All Rights Reserved</p>
    </footer>
</body>
</html>
