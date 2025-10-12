<?php
include 'db.php';
$message = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    die('Invalid token!');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "<div class='error'>Passwords do not match!</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL WHERE reset_token=?");
        $stmt->bind_param("ss", $hashed_password, $token);
        if ($stmt->execute()) {
            $message = "<div class='success'>Password reset successfully! <a href='login.php'>Login now</a></div>";
        } else {
            $message = "<div class='error'>Failed to reset password.</div>";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<form method="POST">
    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Reset Password</button>
</form>
<?php if (!empty($message)) echo $message; ?>
