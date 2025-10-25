<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// ------------------
// Fetch bookings for resources uploaded by this user
// ------------------
$sql = "
SELECT 
    b.booking_id,
    b.booked_at,
    r.title AS resource_title,
    u.name AS booked_by,
    u.email AS booked_email
FROM bookings b
JOIN resources r ON b.resource_id = r.resource_id
JOIN users u ON b.user_id = u.id
WHERE r.added_by = ?
ORDER BY b.booked_at DESC
";

$stmt = $conn->prepare($sql);

// ✅ Check for prepare errors
if (!$stmt) {
    die("SQL Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings - Resource Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    font-family: Arial, sans-serif;
    background: #f8f9fa;
}

/* Header */
header {
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 {
    margin: 0;
    font-size: 24px;
}
header nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    font-weight: bold;
}
header nav a:hover {
    text-decoration: underline;
}

/* Container */
.container {
    flex: 1; /* pushes footer to bottom */
    width: 90%;
    margin: 20px auto;
    text-align: center;
}

/* Table Styles */
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
th {
    background-color: #4CAF50;
    color: white;
}
tr:nth-child(even) {
    background-color: #f2f2f2;
}
tr:hover {
    background-color: #ddd;
}

/* Footer */
footer {
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: 0;
}
footer p {
    margin: 0;
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
    <h2>People Who Booked Your Resources</h2>
    <p>Welcome, <?= htmlspecialchars($user_name); ?> 👋</p>

    <?php if($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Resource</th>
                <th>Booked By</th>
                <th>Email</th>
                <th>Booking Date</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['resource_title']) ?></td>
                    <td><?= htmlspecialchars($row['booked_by']) ?></td>
                    <td><?= htmlspecialchars($row['booked_email']) ?></td>
                    <td><?= $row['booked_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No one has booked your resources yet.</p>
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
