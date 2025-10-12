<?php
$host = "localhost";
$dbname = "book_exchange";  // Make sure this matches your actual database
$username = "root";         // Default XAMPP username
$password = "";             // Default XAMPP password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

?>
