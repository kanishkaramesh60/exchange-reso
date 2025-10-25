<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT r.* FROM resources r
        JOIN favorites f ON r.resource_id = f.resource_id
        WHERE f.user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Wishlist</h2>
<?php while($row = $result->fetch_assoc()): ?>
    <div>
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <a href="remove_favorite.php?id=<?= $row['resource_id'] ?>">Remove</a>
    </div>
<?php endwhile; ?>
