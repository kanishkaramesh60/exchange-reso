<?php
session_start();
include 'db.php';

// ✅ Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// ✅ Display message
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// ✅ Search & filter
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';

// ✅ Categories
$categories = ['Textbook','Notes','Stationery','Other'];

// ✅ Build SQL
$sql = "SELECT r.resource_id, r.title, r.description, r.category, r.status, r.image, u.name AS user_name
        FROM resources r
        JOIN users u ON r.added_by = u.id
        WHERE 1";

$params = [];
$types = "";

// Search filter
if ($search != "") {
    $sql .= " AND (r.title LIKE ? OR u.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Status filter
if ($status != "") {
    $sql .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Category filter
if ($category != "") {
    $sql .= " AND r.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY r.resource_id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
}

/* Header */
header {
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 { margin: 0; font-size: 24px; }
header nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    font-weight: bold;
}
header nav a:hover { text-decoration: underline; }

/* Container */
.container {
    width: 95%;
    max-width: 1200px;
    margin: 20px auto;
    flex: 1;
}

/* Table */
table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}
th {
    background-color: #4CAF50;
    color: white;
}
tr:nth-child(even) { background-color: #f2f2f2; }
tr:hover { background-color: #ddd; }

/* Status styling */
.status {
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
}
.status.pending { background-color: orange; color: white; }
.status.approved { background-color: green; color: white; }
.status.rejected { background-color: red; color: white; }

/* Buttons */
a {
    text-decoration: none;
    padding: 5px 10px;
    margin: 2px;
    border-radius: 3px;
}
a:hover { opacity: 0.8; }
.approve { background-color: green; color: white; }
.reject { background-color: red; color: white; }
.message {
    color: green;
    font-weight: bold;
}

/* Search/filter form */
form input, form select, form button {
    padding: 8px;
    margin: 5px;
    font-size: 14px;
}
form button {
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
form button:hover { background-color: #45a049; }

/* Image preview */
img.resource-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #ccc;
}

/* Footer */
footer {
    color: white;
    text-align: center;
    padding: 20px 0;
    margin-top: auto;
}
</style>
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="modification_requests.php">Modification Requests</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <?php if ($message != '') echo "<p class='message'>$message</p>"; ?>

    <!-- Search & Filter Form -->
    <form method="GET">
        <input type="text" name="search" placeholder="Search by title or user" value="<?= htmlspecialchars($search) ?>">
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
        <a href="admin_dashboard.php" style="color:#007BFF;">Reset</a>
    </form>

    <!-- Resource Table -->
    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Title</th>
                <th>Uploaded By</th>
                <th>Category</th>
                <th>Description</th>
                <th>Image</th>
                <th>Status</th>
                <th>Action</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $statusClass = strtolower($row['status']);

            // ✅ Image check (works for all users)
            $imgHtml = '';
            if (!empty($row['image']) && file_exists($row['image'])) {
                $safePath = htmlspecialchars($row['image']);
                $imgHtml = "<img class='resource-img' src='{$safePath}' alt='Resource Image'>";
            }

            echo "<tr>
                    <td>{$row['resource_id']}</td>
                    <td>" . htmlspecialchars($row['title']) . "</td>
                    <td>" . htmlspecialchars($row['user_name']) . "</td>
                    <td>" . htmlspecialchars($row['category']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td>{$imgHtml}</td>
                    <td><span class='status $statusClass'>" . ucfirst($row['status']) . "</span></td>
                    <td>";

            if ($row['status'] == 'pending') {
                echo "<a class='approve' href='approve_resource.php?id={$row['resource_id']}&action=approve' onclick='return confirm(\"Approve this resource?\");'>Approve</a>";
                echo "<a class='reject' href='approve_resource.php?id={$row['resource_id']}&action=reject' onclick='return confirm(\"Reject this resource?\");'>Reject</a>";
            } else {
                echo "-";
            }

            echo "</td></tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No resources found.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Resource Exchange | Made with ❤️ by Students</p>
</footer>
</body>
</html>
