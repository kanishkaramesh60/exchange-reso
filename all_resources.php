<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// ------------------
// Categories
// ------------------
$categories = ['Textbook','Notes','Stationery','Other'];

// ------------------
// Search & filter
// ------------------
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// ------------------
// Fetch all approved resources with booking info
// ------------------
$sql = "
SELECT r.resource_id, r.title, r.description, r.category, r.image, u.name AS user_name,
       IFNULL(b.booked_count, 0) AS is_booked
FROM resources r
JOIN users u ON r.user_id = u.id
LEFT JOIN (
    SELECT resource_id, COUNT(*) AS booked_count
    FROM bookings
    GROUP BY resource_id
) b ON r.resource_id = b.resource_id
WHERE r.status = 'approved'
";

// Bind parameters dynamically
$params = [];
$types = "";

if ($search != "") {
    $sql .= " AND (r.title LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($category != "") {
    $sql .= " AND r.category=?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Resources - Book Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
    .filter-form input, .filter-form select, .filter-form button {
        padding: 7px;
        margin: 5px;
        font-size: 14px;
    }
    .filter-form button {
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .filter-form button:hover { background-color: #45a049; }
    .resource-grid { display: flex; flex-wrap: wrap; gap: 15px; }
    .resource-card { background: #fff; padding: 15px; border-radius: 8px; width: 250px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .resource-card img { max-width: 100%; border-radius: 5px; margin-top: 10px; }
    .book-btn { background-color: #4CAF50; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
    .book-btn:hover { opacity: 0.9; }
    .booked { font-weight: bold; color: white; }
</style>
</head>
<body>
<div class="container">
    <h2>All Available Resources</h2>
    <p>Welcome, <?= htmlspecialchars($user_name); ?> 👋</p>

    <!-- Search & Filter -->
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search by title or user" value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $category==$cat?'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="all_resources.php">Reset</a>
    </form>

    <div class="resource-grid">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class='resource-card'>
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><strong>Category:</strong> <?= htmlspecialchars($row['category']) ?></p>
                    <p><strong>Uploaded by:</strong> <?= htmlspecialchars($row['user_name']) ?></p>
                    <p><?= substr($row['description'],0,80) ?>...</p>

                    <?php if(!empty($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="Resource Image">
                    <?php endif; ?>

                    <div style="margin-top:10px;">
                        <?php if($row['is_booked'] == 0): ?>
                            <a class="book-btn" href="book_resource.php?id=<?= $row['resource_id'] ?>">Book Now</a>
                        <?php else: ?>
                            <span class="booked">Already Booked</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No resources available.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
