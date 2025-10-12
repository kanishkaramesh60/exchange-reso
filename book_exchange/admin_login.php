<?php
session_start();
include 'db.php';

// ✅ Redirect already logged-in admin
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header("Location: admin_dashboard.php");
    exit();
}

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        if(password_verify($password, $row['password'])){
            // ✅ Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['admin_id'];

            // ✅ Close statement and connection before redirect
            $stmt->close();
            $conn->close();

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid Password";
        }
    } else {
        $error = "Admin not found";
    }

    // Close statement and connection if login fails
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Admin Login</h2>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <!-- Email Field -->
        <input type="email" name="email" placeholder="Enter your email" required>

        <!-- Password Field with toggle -->
        <div class="password-container">
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>

        <button type="submit" name="login">Login</button>
        <a href="forgot_password.php">Forgot Password?</a>
    </form>
</div>

<script>
// Toggle show/hide password
function togglePassword() {
    const passwordField = document.getElementById('password');
    if(passwordField.type === "password"){
        passwordField.type = "text";
    } else {
        passwordField.type = "password";
    }
}
</script>

</body>
</html>
