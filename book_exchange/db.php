<?php
$host = "localhost";
$dbname = "book_exchange";  // Your database name
$username = "root";         // XAMPP default
$password = "";             // XAMPP default

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");
?>
