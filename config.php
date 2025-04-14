<?php
$host = "localhost";
$dbname = "budget_tracker"; // Change to your DB name
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password (leave blank for XAMPP)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
