<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $created_at);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <h2>Welcome, <?php echo $name; ?>!</h2>
    <p><strong>Email:</strong> <?php echo $email; ?></p>
    <p><strong>Joined On:</strong> <?php echo $created_at; ?></p>
    <a href="logout.php">Logout</a>
</div>
