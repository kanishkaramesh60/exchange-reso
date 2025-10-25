<?php
include 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$message = '';

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Use session fallback for email
    $email = trim($_POST['email'] ?? ($_SESSION['reset_email'] ?? ''));

    // Step 1: Check if email exists
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Step 2: Send OTP
            if (!isset($_POST['otp']) && !isset($_POST['password'])) {
                $otp = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

                $stmt2 = $conn->prepare("UPDATE users SET otp_code=?, otp_expiry=? WHERE email=?");
                $stmt2->bind_param("sss", $otp, $expiry, $email);
                $stmt2->execute();
                $stmt2->close();

                // Send OTP mail
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'resourcex04@gmail.com'; // your sender email
                    $mail->Password = 'ymij gbbq rslp zwwz';   // your app password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('resourcex04@gmail.com', 'ResourceX');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for Password Reset';
                    $mail->Body = "<p>Your OTP is <b>$otp</b>. It is valid for 10 minutes.</p>";

                    $mail->send();
                    $_SESSION['reset_email'] = $email;
                    $message = "<div class='success'>✅ OTP sent to your email!</div>";

                } catch (Exception $e) {
                    $message = "<div class='error'>Failed to send OTP. Mailer Error: {$mail->ErrorInfo}</div>";
                }
            }

            // Step 3: Verify OTP
            elseif (isset($_POST['otp']) && !isset($_POST['password'])) {
                $otp = trim($_POST['otp']);
                $email = $_SESSION['reset_email'] ?? '';

                $stmt3 = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email=?");
                $stmt3->bind_param("s", $email);
                $stmt3->execute();
                $stmt3->bind_result($otp_code, $otp_expiry);
                $stmt3->fetch();
                $stmt3->close();

                if ($otp === $otp_code && strtotime($otp_expiry) > time()) {
                    $_SESSION['otp_verified'] = true;
                    $message = "<div class='success'>✅ OTP verified! Set your new password below.</div>";
                } else {
                    $message = "<div class='error'>❌ Invalid or expired OTP.</div>";
                }
            }

            // Step 4: Update Password
            elseif (
                isset($_POST['password']) &&
                isset($_POST['confirm_password']) &&
                ($_SESSION['otp_verified'] ?? false) === true
            ) {
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $email = $_SESSION['reset_email'];

                if ($password !== $confirm_password) {
                    $message = "<div class='error'>Passwords do not match!</div>";
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt4 = $conn->prepare("UPDATE users SET password=?, otp_code=NULL, otp_expiry=NULL WHERE email=?");
                    $stmt4->bind_param("ss", $hashed, $email);
                    $stmt4->execute();
                    $stmt4->close();

                    // clear session after success
                    session_unset();
                    $message = "<div class='success'>🎉 Password updated successfully! <a href='login.php'>Login now</a></div>";
                }
            }
        } else {
            $message = "<div class='error'>Email not found!</div>";
        }

        $stmt->close();
    } else {
        $message = "<div class='error'>Please enter your email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<link rel="stylesheet" href="style.css">

</head>
<body>

<div class="container">
    <h2>Forgot Password</h2>
    <?php if (!empty($message)) echo $message; ?>

    <form method="POST">
        <?php if (!isset($_SESSION['reset_email'])): ?>
            <!-- Step 1: Enter Email -->
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send OTP</button>

        <?php elseif (isset($_SESSION['reset_email']) && !isset($_SESSION['otp_verified'])): ?>
            <!-- Step 2: Enter OTP -->
            <p>Email: <b><?= htmlspecialchars($_SESSION['reset_email']) ?></b></p>
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>

        <?php elseif (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true): ?>
            <!-- Step 3: Reset Password -->
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="New Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
            </div>
            <button type="submit">Reset Password</button>
        <?php endif; ?>
    </form>

    <a href="login.php">Back to Login</a>
</div>

<script>
function togglePassword(id, element) {
    const input = document.getElementById(id);
    const type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
    element.textContent = type === 'password' ? '🙈' : '👁️';
}
</script>

</body>
</html>
