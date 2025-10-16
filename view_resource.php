<?php
session_start();
include 'db.php';

// ✅ Only logged-in users
if(!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true){
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

// Fetch all approved resources except current user's
$sql = "SELECT r.resource_id, r.title, r.description, r.category, r.image, u.name AS user_name
        FROM resources r
        JOIN users u ON r.user_id = u.id
        WHERE r.status='approve' AND r.user_id != ?
        ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Resources</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<h1>Available Resources</h1>
<nav>
    <a href="user_dashboard.php">Dashboard</a> |
    <a href="view_resources.php">Home</a> |
    <a href="logout.php">Logout</a>
</nav>

<?php if($message != ''): ?>
    <p style="color:green;"><?= $message; ?></p>
<?php endif; ?>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Title</th>
        <th>Description</th>
        <th>Category</th>
        <th>Image</th>
        <th>Action</th>
    </tr>

    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['resource_id']; ?></td>
                <td><?= htmlspecialchars($row['user_name']); ?></td>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= htmlspecialchars($row['description']); ?></td>
                <td><?= htmlspecialchars($row['category']); ?></td>
                <td>
                    <?php if(!empty($row['image']) && file_exists($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']); ?>" width="80" alt="Resource Image">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td>
                    <a href="book_resource.php?id=<?= $row['resource_id']; ?>" onclick="return confirm('Book this resource?');">Book</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No resources available.</td></tr>
    <?php endif; ?>
</table>

<?php $conn->close(); ?>
</body>
</html>
