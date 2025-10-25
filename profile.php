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

// ✅ Fetch current user details
$stmt = $conn->prepare("SELECT name, email, location FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $location = trim($_POST['location']);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, location=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $location, $user_id);

    if ($stmt->execute()) {
        $message = "<p class='success'>✅ Profile updated successfully.</p>";
        $_SESSION['user_name'] = $name;
        // Refresh updated data
        $user['name'] = $name;
        $user['email'] = $email;
        $user['location'] = $location;
    } else {
        $message = "<p class='error'>❌ Failed to update: " . htmlspecialchars($stmt->error) . "</p>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        header nav a:hover { text-decoration: underline; }

        .container {
            flex: 1;
            width: 90%;
            max-width: 500px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .profile-field {
            margin: 15px 0;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input {
            width: 100%;
            border: none;
            background: #f0f0f0;
            border-radius: 8px;
            padding: 10px;
            font-size: 15px;
            color: #333;
            transition: background 0.3s;
        }

        input[readonly] {
            background: #e9e9e9;
            cursor: not-allowed;
        }

        button {
            background: #218838;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            transition: background 0.3s;
        }

        button:hover { background: #1e7e34; }

        .success { text-align: center; color: green; font-weight: bold; }
        .error { text-align: center; color: red; font-weight: bold; }

        footer {
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>

<header>
    <h1>👤 My Profile</h1>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="upload_resource.php">Add Resource</a>
        <a href="all_resources.php">All Resources</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Profile Details</h2>
    <?= $message ?>

    <form method="POST" id="profileForm">
        <div class="profile-field">
            <label>Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
        </div>

        <div class="profile-field">
            <label>Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <div class="profile-field">
            <label>Location:</label>
            <input type="text" name="location" id="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" readonly>
        </div>

        <button type="button" id="editBtn" onclick="enableAll()">✏️ Edit Details</button>
        <button type="submit" id="saveBtn" style="display:none;">💾 Save Changes</button>
    </form>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Resource Exchange | Made with ❤️ by Students</p>
</footer>

<script>
function enableAll() {
    const inputs = document.querySelectorAll('#profileForm input');
    inputs.forEach(input => {
        input.readOnly = false;
        input.style.background = '#fff';
    });

    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'block';
}
</script>

</body>
</html>
