<?php
session_start();
include 'db.php';

// ✅ Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// ✅ Display success/error message after approve/reject
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// ✅ Handle search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build SQL dynamically
$sql = "SELECT r.resource_id, r.title, r.description, r.category, r.status, u.name AS user_name
        FROM resources r
        JOIN users u ON r.user_id = u.id
        WHERE 1";

$params = [];
$types = "";

// Search by title or user
if ($search != "") {
    $sql .= " AND (r.title LIKE ? OR u.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Filter by status
if ($status != "") {
    $sql .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Filter by category
if ($category != "") {
    $sql .= " AND r.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY r.resource_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ✅ Define categories exactly like edit resource form
$categories = ['Textbook','Notes','Stationery','Other'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { width: 90%; margin: auto; text-align: center; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
        .status { padding: 5px 10px; border-radius: 5px; font-weight: bold; }
        .status.pending { background-color: orange; color: white; }
        .status.approved { background-color: green; color: white; }
        .status.rejected { background-color: red; color: white; }
        a { text-decoration: none; padding: 5px 10px; margin: 2px; border-radius: 3px; }
        a:hover { opacity: 0.8; }
        .approve { background-color: green; color: white; }
        .reject { background-color: red; color: white; }
        .message { color: green; font-weight: bold; }

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
        form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin Dashboard</h2>
    <p>
        
        <a href="all_resources.php">All Resources</a>
        <a href="logout.php">Logout</a>
    </p>

    <?php if ($message != '') echo "<p class='message'>$message</p>"; ?>

    <!-- Search & Filter Form -->
    <form method="GET">
        <input type="text" name="search" placeholder="Search by title or user" value="<?php echo htmlspecialchars($search); ?>">

        <select name="status">
            <option value="">All Status</option>
            <option value="pending" <?php if($status=='pending') echo 'selected'; ?>>Pending</option>
            <option value="approve" <?php if($status=='approve') echo 'selected'; ?>>Approved</option>
            <option value="reject" <?php if($status=='reject') echo 'selected'; ?>>Rejected</option>
        </select>

        <select name="category">
            <option value="">All Categories</option>
            <?php
            foreach($categories as $cat){
                $selected = ($category==$cat) ? "selected" : "";
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>

        <button type="submit">Filter</button>
        <a href="admin_dashboard.php">Reset</a>
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
                <th>Status</th>
                <th>Action</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $statusClass = strtolower($row['status']);
            echo "<tr>
                    <td>{$row['resource_id']}</td>
                    <td>" . htmlspecialchars($row['title']) . "</td>
                    <td>" . htmlspecialchars($row['user_name']) . "</td>
                    <td>" . htmlspecialchars($row['category']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td><span class='status $statusClass'>" . ucfirst($row['status']) . "</span></td>
                    <td>";

            if ($row['status'] == 'pending') {
                echo "<a class='approve' href='approve_resource.php?id=" . $row['resource_id'] . "&action=approve' onclick='return confirm(\"Approve this resource?\");'>Approve</a>";
                echo "<a class='reject' href='approve_resource.php?id=" . $row['resource_id'] . "&action=reject' onclick='return confirm(\"Reject this resource?\");'>Reject</a>";
            } else {
                echo "-";
            }

            echo "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No resources found.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>
</div>

</body>
</html>
