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
// Categories
// ------------------
$categories = ['Textbook', 'Notes', 'Stationery', 'Other'];

// ------------------
// Search & Filter
// ------------------
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// ------------------
// Fetch all approved resources
// ------------------
$sql = "
SELECT 
    r.resource_id,
    r.title,
    r.description,
    r.category,
    r.image,
    r.added_by,
    u.name AS user_name,
    CASE 
        WHEN EXISTS (SELECT 1 FROM bookings b WHERE b.resource_id = r.resource_id)
        THEN 1 ELSE 0 
    END AS is_booked
FROM resources r
JOIN users u ON r.added_by = u.id
WHERE r.status = 'approved'
";

$params = [];
$types = "";

// ✅ Apply search filter
if ($search != "") {
    $sql .= " AND (r.title LIKE ? OR u.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// ✅ Apply category filter
if ($category != "") {
    $sql .= " AND r.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Error: " . $conn->error);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Resources - Resource Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
body { font-family: Arial, sans-serif; background: #f8f9fa; margin:0; padding:0; }

/* Header */
header { color:white; padding:20px 30px; display:flex; justify-content:space-between; align-items:center; }
header h1 { margin:0; font-size:24px; }
header nav a { color:white; text-decoration:none; margin-left:20px; font-weight:bold; }
header nav a:hover { text-decoration:underline; }

/* Container */
.container { width:90%; margin:20px auto; text-align:center; }

/* Search & Filter */
.filter-form input, .filter-form select, .filter-form button, .filter-form a { padding:7px 10px; margin:5px; font-size:14px; }
.filter-form button { background-color:#d81b60; color:white; border:none; border-radius:4px; cursor:pointer; }
.filter-form button:hover { background-color:#ad1457; }
.filter-form a { text-decoration:none; color:#007BFF; font-weight:bold; }
.filter-form a:hover { text-decoration:underline; }

/* Resource Grid */
.resource-grid { display:flex; flex-wrap:wrap; gap:15px; justify-content:center; margin-top:20px; }
.resource-card { background:#fff; padding:15px; border-radius:8px; width:250px; box-shadow:0 2px 5px rgba(0,0,0,0.1); text-align:center; transition:transform 0.2s; }
.resource-card:hover { transform:translateY(-3px); }
.resource-card img { max-width:100%; height:150px; object-fit:cover; border-radius:5px; margin-top:10px; }
.book-btn { background-color:#28a745; color:#fff; padding:8px 16px; border-radius:5px; text-decoration:none; font-weight:bold; display:inline-block; margin-top:5px; }
.book-btn:hover { background-color:#218838; }
.booked { display:inline-block; padding:8px 16px; background-color:#dc3545; color:#fff; border-radius:5px; font-weight:bold; margin-top:5px; }
.favorite-btn { display:inline-block; padding:8px 16px; border-radius:5px; font-weight:bold; margin-top:5px; text-decoration:none; }

/* Footer */
footer { color:#fff; text-align:center; padding:20px 0; margin-top:40px;  }
footer p { margin:0; }
</style>
</head>
<body>

<header>
    <h1>📚 Resource Exchange</h1>
    <nav>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="upload_resource.php">Add Resource</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

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
                    <p><?= htmlspecialchars(substr($row['description'],0,80)) ?>...</p>

                    <?php if(!empty($row['image']) && file_exists($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="Resource Image">
                    <?php endif; ?>

                    <div>
                        <?php if($row['added_by'] == $user_id): ?>
                            <span class="booked">Your Resource</span>
                        <?php elseif($row['is_booked'] == 1): ?>
                            <span class="booked">Already Booked</span>
                        <?php else: ?>
                            <a class="book-btn" href="book_resource.php?id=<?= $row['resource_id'] ?>">Book Now</a>
                        <?php endif; ?>

                        <a class="book-btn" style="background:#007BFF;"
                           href="contact_owner.php?receiver_id=<?= $row['added_by'] ?>&title=<?= urlencode($row['title']) ?>">
                           Contact Owner
                        </a>

                        <?php
                        // Check if resource is favorited
                        $stmtFav = $conn->prepare("SELECT id FROM favorites WHERE user_id=? AND resource_id=?");
                        $stmtFav->bind_param("ii", $user_id, $row['resource_id']);
                        $stmtFav->execute();
                        $stmtFav->store_result();
                        $isFavorited = $stmtFav->num_rows > 0;
                        $stmtFav->close();
                        ?>

                        <?php if($isFavorited): ?>
                            <a class="favorite-btn" style="background:#FFC107;color:#000;"
                               href="remove_favorite.php?id=<?= $row['resource_id'] ?>"
                               onclick="return confirm('Remove from wishlist?');">
                               ★ Favorited
                            </a>
                        <?php else: ?>
                            <a class="favorite-btn" style="background:#17a2b8;color:#fff;"
                               href="add_favorite.php?id=<?= $row['resource_id'] ?>">
                               ☆ Add to Wishlist
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No resources available.</p>
        <?php endif; ?>
    </div>
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
