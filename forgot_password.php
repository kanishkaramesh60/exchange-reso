<?php
include 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Show password fields after email exists
        if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $message = "<div class='error'>Passwords do not match!</div>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE email=?");
                $stmt2->bind_param("ss", $hashed_password, $email);
                $stmt2->execute();
                $stmt2->close();

                $message = "<div class='success'>Password updated successfully! <a href='login.php'>Login now</a></div>";
            }
        }
    } else {
        $message = "<div class='error'>Email not found!</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .password-container {
            position: relative;
            width: 100%;
            margin-top: 8px;
        }
        .password-container input {
            width: 100%;
            padding-right: 40px; /* space for eye icon */
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?php if (!empty($message)) echo $message; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        
        <?php if (isset($_POST['email'])): ?>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="New Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
            </div>
        <?php endif; ?>
        
        <button type="submit">Reset Password</button>
    </form>

    <a href="login.php">Back to Login</a>
</div>

<script>
    function togglePassword(id, element) {
        const input = document.getElementById(id);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        element.textContent = type === 'password' ? '🙈' : '👁️';
    }
</script>
</body>
</html>
