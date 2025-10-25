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

// -------------------
// Fetch resource
// -------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid resource ID.");
}

$resource_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM resources WHERE resource_id=? AND added_by=?");
$stmt->bind_param("ii", $resource_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Resource not found or you don't have permission to edit it.");
}

$resource = $result->fetch_assoc();
$current_image = $resource['image'];
$stmt->close();

// -------------------
// Handle form submission
// -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $new_image_path = $current_image;

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = basename($_FILES['image']['name']);
        $filename = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $filename);
        $target_file = $target_dir . time() . "_" . $filename;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg','jpeg','png','gif'];

        if (!in_array($file_type, $allowed_types)) {
            $message = "<p class='error'>❌ Only JPG, JPEG, PNG, GIF files are allowed.</p>";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $new_image_path = $target_file;
                if (!empty($current_image) && file_exists($current_image)) {
                    unlink($current_image);
                }
            } else {
                $message = "<p class='error'>❌ Failed to upload new image.</p>";
            }
        }
    }

    // Update resource
    if ($message === '') {
        $stmt = $conn->prepare("UPDATE resources SET title=?, description=?, category=?, image=? WHERE resource_id=? AND added_by=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);

        $stmt->bind_param("ssssii", $title, $description, $category, $new_image_path, $resource_id, $user_id);

        if ($stmt->execute()) {
            $message = "<p class='success'>✅ Resource updated successfully.</p>";
            $current_image = $new_image_path;
        } else {
            $message = "<p class='error'>❌ Failed to update resource: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

// Image preview
if (!empty($current_image) && file_exists($current_image)) {
    $image_preview = "<img src='" . htmlspecialchars($current_image) . "' width='150' style='margin-top:10px;' alt='Resource Image'>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Resource - Book Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
.error { color: #e53935; font-weight:bold; }
.success { color: #388e3c; font-weight:bold; }
</style>
</head>
<body>
<header>
    <h1>📚 Resource Exchange - Edit Resource</h1>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="all_resources.php">All Resources</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Edit Resource</h2>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:20px;">
            <label for="title">Title:</label><br>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($resource['title']) ?>" required style="width:30%;">
        </div>

        <div style="margin-bottom:20px;">
            <label for="description">Description:</label><br>
            <textarea name="description" id="description" rows="4" required style="width:30%;"><?= htmlspecialchars($resource['description']) ?></textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label for="category">Category:</label><br>
            <select name="category" id="category" required style="width:30%;">
                <option value="">--Select Category--</option>
                <option value="Textbook" <?= $resource['category']=='Textbook' ? 'selected':'' ?>>Textbook</option>
                <option value="Notes" <?= $resource['category']=='Notes' ? 'selected':'' ?>>Notes</option>
                <option value="Stationery" <?= $resource['category']=='Stationery' ? 'selected':'' ?>>Stationery</option>
                <option value="Other" <?= $resource['category']=='Other' ? 'selected':'' ?>>Other</option>
            </select>
        </div>

        <div style="margin-bottom:15px;">
            <label for="image">Upload Image (optional):</label><br>
            <input type="file" name="image" id="image" accept="image/*"><br>
            <?= $image_preview ?>
        </div>

        <div>
            <button type="submit" style="padding:10px 20px;">Update Resource</button>
        </div>
    </form>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Resource Exchange | All Rights Reserved</p>
</footer>
</body>
</html>
