<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in admins
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header("Location: admin_login.php");
    exit();
}

// ✅ Display success/error message after approve/reject
$message = '';
if(isset($_SESSION['message'])){
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// ✅ Fetch pending resources
$sql = "SELECT r.resource_id, r.title, r.description, r.category, r.status, u.name AS user_name
        FROM resources r
        JOIN users u ON r.user_id = u.id
        WHERE r.status = 'pending'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container" style="width: 95%; text-align: left;">
    <h2>Admin Dashboard</h2>
    <p>
        <a href="admin_logout.php">Logout</a>
    </p>

    <?php if($message != '') echo "<p class='success'>$message</p>"; ?>

    <hr>
    <h3>Pending Resources</h3>

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

        while($row = $result->fetch_assoc()){
            echo "<tr>
                    <td>{$row['resource_id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['user_name']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <a href='approve_resource.php?id={$row['resource_id']}' onclick='return confirm(\"Approve this resource?\");'>Approve</a> |
                        <a href='reject_resource.php?id={$row['resource_id']}' onclick='return confirm(\"Reject this resource?\");'>Reject</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No pending resources found.</p>";
    }

    $conn->close();
    ?>
</div>

</body>
</html>
