<?php
session_start();
include 'db.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Get receiver ID from URL
if (!isset($_GET['receiver_id']) || !is_numeric($_GET['receiver_id'])) {
    die("Invalid user.");
}

$receiver_id = intval($_GET['receiver_id']);

// Prevent users from contacting themselves
if ($receiver_id == $_SESSION['user_id']) {
    die("You cannot contact yourself.");
}

// ✅ Get optional resource title from URL
$resource_title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';

// ✅ Fetch receiver details
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$receiver = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact <?= htmlspecialchars($receiver['name']); ?></title>
<style>
body { font-family: Arial, sans-serif; background-color: #eef1f7; padding: 40px; }
.contact-container {
    background: #fff; max-width: 500px; margin: auto; padding: 30px; border-radius: 10px;
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
}
h2 { text-align: center; color: #333; }
label { display: block; margin-top: 15px; color: #444; }
input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; }
button { margin-top: 20px; width: 100%; background-color: #007bff; color: #fff; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }
button:hover { background-color: #0056b3; }
a { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; text-align: center; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="contact-container">
    <h2>Contact <?= htmlspecialchars($receiver['name']); ?></h2>

    <form method="POST" action="contact_action.php">
        <input type="hidden" name="receiver_id" value="<?= $receiver_id; ?>">

        <label>Subject</label>
        <input type="text" name="subject" 
               value="<?= $resource_title ? "Regarding: $resource_title" : ""; ?>" required>

        <label>Message</label>
        <textarea name="message" rows="6" required></textarea>

        <button type="submit">Send Message</button>
    </form>

    <a href="all_resources.php">← Back to Resources</a>
</div>

</body>
</html>
