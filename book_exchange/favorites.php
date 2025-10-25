<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Fetch favorite resources
$sql = "
SELECT r.resource_id, r.title, r.description, r.category, r.image, r.added_by, u.name AS user_name
FROM favorites f
JOIN resources r ON f.resource_id = r.resource_id
JOIN users u ON r.added_by = u.id
WHERE f.user_id = ?
ORDER BY f.added_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Favorites</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Ensure full height for body and html */
html, body {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header styling */
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

/* Main container */
.container {
    flex: 1; /* Ensures footer is pushed to bottom if content is short */
    width: 90%;
    max-width: 1000px;
    margin: 20px auto;
}

/* Grid for resources */
.resource-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

/* Individual resource card */
.resource-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    width: calc(33% - 20px);
    box-sizing: border-box;
}

.resource-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 5px;
    margin-top: 10px;
}

/* Buttons for actions */
.resource-actions {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.book-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    display: inline-block;
    text-align: center;
}

.book-btn:hover {
    opacity: 0.8;
}

/* Footer styling */
footer {
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: 0;
}

    </style>
</head>
<body>
<header>
    <h1>📚 My Favorites</h1>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="all_resources.php">All Resources</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <?php if($result->num_rows > 0): ?>
        <div class="resource-grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="resource-card">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><strong>Category:</strong> <?= htmlspecialchars($row['category']) ?></p>
                    <p><strong>Uploaded by:</strong> <?= htmlspecialchars($row['user_name']) ?></p>
                    <p><?= htmlspecialchars(substr($row['description'],0,80)) ?>...</p>
                    <?php if(!empty($row['image']) && file_exists($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="Resource Image">
                    <?php endif; ?>
                    <div class="resource-actions">
                        <centre>
                        <a class="book-btn" style="background:#4CAF50;"
                           href="book_resource.php?resource_id=<?= $row['resource_id'] ?>">
                           Book Resource
                        </a>
                        <a class="book-btn" style="background:#2196F3;"
                           href="contact_owner.php?owner_id=<?= $row['added_by'] ?>&resource_id=<?= $row['resource_id'] ?>">
                           Contact Owner
                        </a>
                        <a class="book-btn" style="background:#ff9800;"
                           href="toggle_favorite.php?resource_id=<?= $row['resource_id'] ?>">
                           Remove from Favorites
                        </a>
                        </centre>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You have no favorite resources yet.</p>
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
