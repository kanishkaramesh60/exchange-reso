<?php
session_start();
include 'db.php';

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Get receiver (resource owner) info if provided via URL
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
$subject = isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef1f7;
            padding: 50px;
        }
        .contact-container {
            background: #fff;
            width: 450px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #333; }
        label { display: block; margin: 10px 0 5px; }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="contact-container">
        <h2>Contact Resource Owner</h2>
        <form method="POST" action="contact_action.php">
            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">

            <label>Subject</label>
            <input type="text" name="subject" value="<?php echo $subject; ?>" required>

            <label>Message</label>
            <textarea name="message" rows="5" required></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>
