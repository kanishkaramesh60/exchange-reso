<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Fetch bookings made by this user
$sql = "
SELECT 
    b.booking_id,
    b.booked_at,
    r.title AS resource_title,
    r.resource_id,
    u.name AS resource_owner
FROM bookings b
JOIN resources r ON b.resource_id = r.resource_id
JOIN users u ON r.added_by = u.id
WHERE b.user_id = ?
ORDER BY b.booked_at DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Prepare failed: ".$conn->error);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings</title>
<link rel="stylesheet" href="dashboard.css">
<style>
/* Flex layout for sticky footer */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #f8f9fa;
}

header {
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 { margin: 0; font-size: 24px; }
header nav a { color: white; text-decoration: none; margin-left: 20px; font-weight: bold; }
header nav a:hover { text-decoration: underline; }

.container {
    width: 90%;
    max-width: 1000px;
    margin: 20px auto;
    flex: 1; /* pushes footer down */
}

h2 { margin-top: 0; }

table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
tr:nth-child(even) { background: #f2f2f2; }
tr:hover { background: #ddd; }

.undo-btn {
    background: #d9534f;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
}
.undo-btn:hover { opacity: 0.8; }

footer {
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: auto; /* push footer to bottom */
}

.message {
    font-weight: bold;
    color: green;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<header>
    <h1>📋 My Bookings</h1>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="upload_resource.php">Add Resource</a>
        <a href="all_resources.php">All Resources</a>
        <a href="profile.php">My Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
<h2>📋 My Bookings</h2>
<p>Welcome, <?= htmlspecialchars($user_name); ?> 👋</p>

<?php if(!empty($_SESSION['message'])): ?>
    <p class="message"><?= $_SESSION['message']; ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if($result->num_rows > 0): ?>
<table>
<tr>
    <th>Resource</th>
    <th>Resource Owner</th>
    <th>Booking Date</th>
    <th>Action</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['resource_title']); ?></td>
    <td><?= htmlspecialchars($row['resource_owner']); ?></td>
    <td><?= $row['booked_at']; ?></td>
    <td>
        <a class="undo-btn" href="undo_booking.php?booking_id=<?= $row['booking_id']; ?>&resource_id=<?= $row['resource_id']; ?>" onclick="return confirm('Are you sure you want to undo this booking?');">Undo Booking</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>You have not booked any resources yet.</p>
<?php endif; ?>
</div>

<footer>
<p>&copy; <?= date('Y'); ?> Resource Exchange | Made with ❤️ by Students</p>
</footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
