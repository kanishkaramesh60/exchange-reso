<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch user name safely from DB
$stmt = $conn->prepare("SELECT name FROM users WHERE id=?");
if (!$stmt) { die("Prepare failed (user fetch): ".$conn->error); }
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

// Display any message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Define categories
$categories = ['Textbook','Notes','Stationery','Other'];

// ------------------
// User's own resources search & filter
// ------------------
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql_user = "SELECT * FROM resources WHERE added_by=?";
$params_user = [$user_id];
$types_user = "i";

if ($search != "") {
    $sql_user .= " AND title LIKE ?";
    $params_user[] = "%$search%";
    $types_user .= "s";
}

if ($status != "") {
    $sql_user .= " AND status=?";
    $params_user[] = $status;
    $types_user .= "s";
}

if ($category != "") {
    $sql_user .= " AND category=?";
    $params_user[] = $category;
    $types_user .= "s";
}

$sql_user .= " ORDER BY created_at DESC";

$stmt_user = $conn->prepare($sql_user);
if (!$stmt_user) { die("Prepare failed (user resources): ".$conn->error); }
$stmt_user->bind_param($types_user, ...$params_user);
$stmt_user->execute();
$user_resources = $stmt_user->get_result();
$stmt_user->close();

// ------------------
// Other users' approved resources
// ------------------
$search_others = isset($_GET['search_others']) ? $_GET['search_others'] : '';
$category_others = isset($_GET['category_others']) ? $_GET['category_others'] : '';

$sql_others = "
SELECT r.resource_id, r.title, r.description, r.category, r.image, u.name AS user_name,
       IFNULL(b.booked_count, 0) AS booked_count
FROM resources r
JOIN users u ON r.added_by = u.id
LEFT JOIN (
    SELECT resource_id, COUNT(*) AS booked_count
    FROM bookings
    GROUP BY resource_id
) b ON r.resource_id = b.resource_id
WHERE r.status = 'approved' AND r.added_by != ?
";

$params_others = [$user_id];
$types_others = "i";

if ($search_others != "") {
    $sql_others .= " AND r.title LIKE ?";
    $params_others[] = "%$search_others%";
    $types_others .= "s";
}

if ($category_others != "") {
    $sql_others .= " AND r.category=?";
    $params_others[] = $category_others;
    $types_others .= "s";
}

$sql_others .= " ORDER BY r.created_at DESC";

$stmt_others = $conn->prepare($sql_others);
if (!$stmt_others) { die("Prepare failed (other resources): ".$conn->error); }
$stmt_others->bind_param($types_others, ...$params_others);
$stmt_others->execute();
$result_others = $stmt_others->get_result();
$stmt_others->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard - Book Exchange</title>
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
    .resource-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(250px,1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .resource-card {
        border:1px solid #ccc;
        border-radius:5px;
        padding:10px;
        background:#fff;
        position:relative;
    }
    .resource-card img { max-width:100%; height:auto; margin-bottom:10px; }
    .status { padding:2px 6px; border-radius:4px; font-size:12px; font-weight:bold; }
    .status.pending { background:#f0ad4e; color:#fff; }
    .status.approved { background:#5cb85c; color:#fff; }
    .status.rejected { background:#d9534f; color:#fff; }
    .card-actions { margin-top:10px; }
    .card-actions a { padding:5px 10px; margin-right:5px; text-decoration:none; border-radius:4px; font-size:13px; }
    .edit-btn { background:#0275d8; color:#fff; }
    .delete-btn { background:#d9534f; color:#fff; }
    .book-btn { background:#5cb85c; color:#fff; }
    .booked { color:#777; font-weight:bold; }
    
    .contact-btn {
    background-color: #17a2b8;
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
}
.contact-btn:hover {
    background-color: #138496;
}

</style>
</head>
<body>
<header>
    <h1>📚 Book Exchange Dashboard</h1>
    <nav>
        <a href="user_dashboard.php">Home</a>
        <a href="upload_resource.php">Add Resource</a>
        <a href="all_resources.php">All Resources</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="profile.php">My Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($user_name); ?> 👋</h2>
    <?php if($message != '') echo "<p class='success'>$message</p>"; ?>

    <a href="upload_resource.php" class="btn add-btn">+ Add New Resource</a>

    <!-- User's own resources -->
    <h3>Your Resources</h3>
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search your resources" value="<?= htmlspecialchars($search) ?>">
        <select name="status">
            <option value="">All Status</option>
            <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
            <option value="approved" <?= $status=='approved'?'selected':'' ?>>Approved</option>
            <option value="rejected" <?= $status=='rejected'?'selected':'' ?>>Rejected</option>
        </select>
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $category==$cat?'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="user_dashboard.php">Reset</a>
    </form>

    <div class="resource-grid">
        <?php
        if ($user_resources->num_rows > 0) {
            while ($row = $user_resources->fetch_assoc()) {
                echo "
                <div class='resource-card'>
                    ".($row['image']? "<img src='uploads/".htmlspecialchars($row['image'])."' alt='Resource'>" : "")."
                    <h3>" . htmlspecialchars($row['title']) . "</h3>
                    <p><strong>Category:</strong> " . htmlspecialchars($row['category']) . "</p>
                    <p><strong>Description:</strong> " . substr($row['description'], 0, 60) . "...</p>
                    <span class='status {$row['status']}'>" . ucfirst($row['status']) . "</span>
                    <div class='card-actions'>
                        <a href='edit_resource.php?id={$row['resource_id']}' class='edit-btn'>Edit</a>
                        <a href='delete_resource.php?id={$row['resource_id']}' class='delete-btn' onclick='return confirm(\"Delete this resource?\")'>Delete</a>
                    </div>
                </div>";
            }
        } else { echo "<p>No resources found. Start by adding one!</p>"; }
        ?>
    </div>

    <!-- Other users' resources -->
    <h3>Other Users' Resources</h3>
    <form method="GET" class="filter-form">
        <input type="text" name="search_others" placeholder="Search other resources" value="<?= htmlspecialchars($search_others) ?>">
        <select name="category_others">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $category_others==$cat?'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="user_dashboard.php">Reset</a>
    </form>

    <div class="resource-grid">
        <?php if($result_others->num_rows > 0): ?>
            <?php while($row = $result_others->fetch_assoc()): ?>
                <div class='resource-card'>
                    <?= $row['image']? "<img src='uploads/".htmlspecialchars($row['image'])."' alt='Resource'>" : "" ?>
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><strong>Category:</strong> <?= htmlspecialchars($row['category']) ?></p>
                    <p><strong>Uploaded by:</strong> <?= htmlspecialchars($row['user_name']) ?></p>
                    <p><strong>Description:</strong> <?= substr($row['description'], 0, 60) ?>...</p>
                    <span class='status approved'>Approved</span>
                    <div class='card-actions'>
                        <?php if ($row['booked_count'] == 0): ?>
                            <a href='book_resource.php?id=<?= $row['resource_id'] ?>' class='book-btn'>Book</a>
                        <?php else: ?>
                            <span class='booked'>Already Booked</span>
                        <?php endif; ?>
                        <!-- ✅ New Contact Owner Button -->
                        <a href='contact.php?receiver_id=<?= $row['added_by'] ?>&subject=Regarding: <?= urlencode($row['title']) ?>' 
                        class='contact-btn'>Contact Owner</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No resources from other users are available yet.</p>
        <?php endif; ?>
    </div>

</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Book Exchange | Made with ❤️ by Students</p>
</footer>
</body>
</html>
