<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Fetch all pending modification requests
$sql = "SELECT mr.id, mr.request_date, mr.status, r.title, u.name AS user_name
        FROM modification_requests mr
        JOIN resources r ON mr.resource_id = r.resource_id
        JOIN users u ON mr.user_id = u.id
        ORDER BY mr.request_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Modification Requests - Admin</title>
<link rel="stylesheet" href="dashboard.css">
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
}

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

.container {
    width: 95%;
    max-width: 1200px;
    margin: 20px auto;
    flex: 1;
}

table {
    border-collapse: collapse;
    width: 100%;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
th, td {
    border-bottom: 1px solid #ddd;
    padding: 12px 15px;
    text-align: center;
}
th {
    background-color: #4CAF50;
    color: white;
    font-weight: bold;
}
tr:hover { background-color: #f1f1f1; }

.status {
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
    text-transform: capitalize;
}
.status.pending { background-color: orange; color: white; }
.status.approved { background-color: green; color: white; }
.status.rejected { background-color: red; color: white; }

a.action-btn {
    padding: 5px 10px;
    margin: 2px;
    border-radius: 4px;
    text-decoration: none;
    color: white;
    font-weight: bold;
}
a.approve { background-color: #28a745; }
a.reject { background-color: #dc3545; }
a.approve:hover { opacity: 0.8; }
a.reject:hover { opacity: 0.8; }

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* full viewport height */
}

.container {
    width: 95%;
    max-width: 1200px;
    margin: 20px auto;
    flex: 1; /* take remaining space to push footer down */
}

/* Footer */
footer {
    text-align: center;
    padding: 20px 0;
    color: white;
    margin-top: auto; /* ensures footer stays at bottom */
}


@media screen and (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }
    th {
        text-align: left;
    }
    td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        text-align: left;
        font-weight: bold;
    }
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
<h2>Modification Requests</h2>
<table>
<tr>
    <th>ID</th>
    <th>Resource Title</th>
    <th>User</th>
    <th>Status</th>
    <th>Request Date</th>
    <th>Action</th>
</tr>
<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td data-label="ID"><?= $row['id'] ?></td>
        <td data-label="Resource Title"><?= htmlspecialchars($row['title']) ?></td>
        <td data-label="User"><?= htmlspecialchars($row['user_name']) ?></td>
        <td data-label="Status"><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
        <td data-label="Request Date"><?= $row['request_date'] ?></td>
        <td data-label="Action">
            <?php if($row['status'] == 'pending'): ?>
                <a class="action-btn approve" href="process_mod_request.php?id=<?= $row['id'] ?>&action=approve" onclick="return confirm('Approve this modification request?')">Approve</a>
                <a class="action-btn reject" href="process_mod_request.php?id=<?= $row['id'] ?>&action=reject" onclick="return confirm('Reject this modification request?')">Reject</a>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No modification requests found.</td></tr>
<?php endif; ?>
</table>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Resource Exchange | Made with ❤️ by Students</p>
</footer>
</body>
</html>
