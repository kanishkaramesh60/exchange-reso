<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT c.subject, c.message, u.name AS sender_name, c.created_at 
        FROM contact_messages c
        JOIN users u ON c.sender_id = u.id
        WHERE c.receiver_id = ?
        ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head><title>Inbox</title></head>
<body>
<h2>Your Messages</h2>
<?php while ($row = $result->fetch_assoc()) { ?>
    <div style="border:1px solid #ccc; padding:10px; margin:10px;">
        <b>From:</b> <?php echo htmlspecialchars($row['sender_name']); ?><br>
        <b>Subject:</b> <?php echo htmlspecialchars($row['subject']); ?><br>
        <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
        <small>Sent: <?php echo $row['created_at']; ?></small>
    </div>
<?php } ?>
</body>
</html>
