<?php
session_start();
include 'db.php';

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Count unread inbox messages
$stmt_inbox = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE receiver_id=? AND is_read=0");
$stmt_inbox->bind_param("i", $user_id);
$stmt_inbox->execute();
$stmt_inbox->bind_result($inbox_count);
$stmt_inbox->fetch();
$stmt_inbox->close();

// Fetch user name
$stmt = $conn->prepare("SELECT name FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

$categories = ['Textbook','Notes','Stationery','Other'];

// ------------------
// User's own resources
// ------------------
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';

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
$stmt_user->bind_param($types_user, ...$params_user);
$stmt_user->execute();
$user_resources = $stmt_user->get_result();
$stmt_user->close();

// ------------------
// Other users' resources
// ------------------
$search_others = $_GET['search_others'] ?? '';
$category_others = $_GET['category_others'] ?? '';

$sql_others = "
SELECT r.resource_id, r.title, r.description, r.category, r.image, r.added_by, u.name AS user_name,
       IFNULL(b.booked_count,0) AS booked_count
FROM resources r
JOIN users u ON r.added_by=u.id
LEFT JOIN (SELECT resource_id, COUNT(*) AS booked_count FROM bookings GROUP BY resource_id) b
ON r.resource_id=b.resource_id
WHERE r.status='approved' AND r.added_by != ?
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
$stmt_others->bind_param($types_others, ...$params_others);
$stmt_others->execute();
$result_others = $stmt_others->get_result();
$stmt_others->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard - Resource Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
body { font-family: Arial,sans-serif; margin:0; padding:0; background:#f8f9fa; }
header { display:flex; justify-content:space-between; padding:20px; color:white; }
header nav a { color:white; text-decoration:none; margin-left:15px; font-weight:bold; }
header nav a:hover { text-decoration:underline; }
.container { width:95%; max-width:1200px; margin:20px auto; }
.filter-form input, .filter-form select, .filter-form button { padding:7px; margin:5px; font-size:14px; }
.filter-form button { background:#4CAF50; color:white; border:none; border-radius:4px; cursor:pointer; }
.filter-form button:hover { background:#45a049; }
.resource-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:15px; margin-top:15px; }
.resource-card { border:1px solid #ddd; border-radius:5px; padding:10px; background:#fff; }
.resource-card img { width:100%; height:180px; object-fit:cover; border-radius:6px; margin-bottom:10px; display:block; }
.status { padding:2px 6px; border-radius:4px; font-size:12px; font-weight:bold; }
.status.pending { background:#f0ad4e; color:white; }
.status.approved { background:#5cb85c; color:white; }
.status.rejected { background:#d9534f; color:white; }
.card-actions a { padding:5px 10px; margin-right:5px; text-decoration:none; border-radius:4px; font-size:13px; display:inline-block; }
.edit-btn { background:#0275d8; color:white; }
.delete-btn { background:#d9534f; color:white; }
.request-btn { background:#6f42c1; color:white; }
.book-btn { background:#5cb85c; color:white; }
.booked { color:#28a745; font-weight:bold; }
.contact-btn { background:#17a2b8; color:white; padding:5px 10px; border-radius:4px; }
.contact-btn:hover { background:#138496; }
.success { background:#d4edda; padding:10px; border-radius:6px; margin-bottom:15px; color:#155724; }
footer { text-align:center; padding:15px; color:white; margin-top:30px; }
</style>
</head>
<body>

<header>
<h1>📚 Resource Exchange</h1>
<nav>
<a href="upload_resource.php">Add Resource</a>
<a href="all_resources.php">All Resources</a>
<a href="my_bookings.php">Orders</a>
<a href="user_booking.php">My Bookings</a>
<a href="profile.php">Profile</a>
<a href="inbox.php">📨 Inbox<?php if($inbox_count>0): ?><span class="badge"><?= $inbox_count ?></span><?php endif; ?></a>
<a href="favorites.php" style="float:right; color:white; padding:8px 12px; border-radius:5px;">★</a>
<a href="logout.php">Logout</a>
</nav>
</header>

<div class="container">
<h2>Welcome, <?= htmlspecialchars($user_name); ?> 👋</h2>
<?php if($message != ''): ?><p class="success"><?= $message ?></p><?php endif; ?>

<!-- User's Resources -->
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
<?php if($user_resources->num_rows>0): ?>
<?php while($row=$user_resources->fetch_assoc()): ?>
<div class="resource-card">
<?php if(!empty($row['image']) && file_exists($row['image'])): ?>
    <img src="<?= htmlspecialchars($row['image']); ?>" alt="Resource">
<?php endif; ?>
<h3><?= htmlspecialchars($row['title']); ?></h3>
<p><strong>Category:</strong> <?= htmlspecialchars($row['category']); ?></p>
<p><strong>Description:</strong> <?= htmlspecialchars(substr($row['description'],0,60)); ?>...</p>
<span class="status <?= $row['status']; ?>"><?= ucfirst($row['status']); ?></span>

<div class="card-actions">
<?php if($row['status'] === 'pending' || $row['status'] === 'rejected'): ?>
    <a href="edit_resource.php?id=<?= $row['resource_id']; ?>" class="edit-btn">Edit</a>
    <a href="delete_my_resource.php?id=<?= $row['resource_id']; ?>" class="delete-btn" onclick="return confirm('Delete this resource?')">Delete</a>
<?php elseif($row['status'] === 'approved'): ?>
    <a href="request_modification.php?id=<?= $row['resource_id']; ?>" class="request-btn" onclick="return confirm('Send modification request to admin?')">Request Modification</a>
<?php endif; ?>
</div>
</div>
<?php endwhile; ?>
<?php else: ?><p>No resources found.</p><?php endif; ?>
</div>

<!-- Other Users' Resources -->
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
<div class="resource-card">
<?php if(!empty($row['image']) && file_exists($row['image'])): ?>
    <img src="<?= htmlspecialchars($row['image']); ?>" alt="Resource">
<?php endif; ?>
<h3><?= htmlspecialchars($row['title']); ?></h3>
<p><strong>Category:</strong> <?= htmlspecialchars($row['category']); ?></p>
<p><strong>Uploaded by:</strong> <?= htmlspecialchars($row['user_name']); ?></p>
<p><strong>Description:</strong> <?= htmlspecialchars(substr($row['description'], 0, 60)); ?>...</p>
<span class="status approved">Approved</span>
<div class="card-actions">
<?php if($row['added_by'] != $user_id && $row['booked_count'] == 0): ?>
    <a href="book_resource.php?id=<?= $row['resource_id']; ?>" class="book-btn">Book</a>
<?php elseif($row['added_by'] == $user_id): ?>
    <span class="booked">Your Resource</span>
<?php else: ?>
    <span class="booked">Already Booked</span>
<?php endif; ?>
<a class="contact-btn" href="contact_owner.php?receiver_id=<?= $row['added_by']; ?>&title=<?= urlencode($row['title']); ?>">Contact Owner</a>
</div>
</div>
<?php endwhile; ?>
<?php else: ?><p>No resources from other users are available yet.</p><?php endif; ?>
</div>
</div>

<footer>
<p>&copy; <?= date('Y'); ?> Resource Exchange | Made with ❤️ by Students</p>
</footer>
</body>
</html>
