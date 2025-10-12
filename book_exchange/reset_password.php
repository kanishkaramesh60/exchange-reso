<?php
include 'db.php';
$message = '';

if (!isset($_GET['token'])) {
    die("Invalid request");
}

$token = $_GET['token'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "<div class='error'>Passwords do not match!</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL WHERE reset_token=?");
        if (!$stmt) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("ss", $hashed_password, $token);
        if ($stmt->execute()) {
            $message = "<div class='success'>✅ Password reset successful! <a href='login.php'>Login now</a></div>";
        } else {
            $message = "<div class='error'>Something went wrong.</div>";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>

        <?php if (!empty($message)) echo $message; ?>

        <form method="POST">
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="New Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>

            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
            </div>

            <button type="submit">Reset Password</button>
        </form>
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
