<?php
include 'db.php';

// Initialize variables
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $location = trim($_POST['location']); // NEW: Location

    // ✅ Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($location)) {
        $message = "<div class='error'>⚠️ All fields are required!</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='error'>⚠️ Passwords do not match!</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // ✅ Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "<div class='error'>❌ Email already registered!</div>";
        } else {
            // ✅ Register new user with location
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, location) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $location);

            if ($stmt->execute()) {
                $message = "<div class='success'>✅ Registration successful! Redirecting to login...</div>";
                header("refresh:3;url=login.php");
            } else {
                $message = "<div class='error'>⚠️ Something went wrong. Please try again.</div>";
            }

            $stmt->close();
        }
        $check->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Registration | Book Exchange</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Create an Account</h2>

    <!-- Display Message -->
    <?= $message ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="location" placeholder="Your Location" required>

        <div class="password-container">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
        </div>

        <div class="password-container">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
        </div>

        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have an account? Login</a>
</div>

<script>
function togglePassword(id, element) {
    const input = document.getElementById(id);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    element.textContent = type === 'password' ? '👁️' : '🙈';
}
</script>
</body>
</html>
