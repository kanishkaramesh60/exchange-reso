<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        header("Location: profile.php");
        exit();
    } else {
        $message = "❌ Invalid email or password!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Book Exchange</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <!-- Display Message -->
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>

            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>

            <button type="submit">Login</button>
        </form>

        <a href="register.php">Don't have an account? Register</a>
        <a href="forgot_password.php">Forgot Password?</a>
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
