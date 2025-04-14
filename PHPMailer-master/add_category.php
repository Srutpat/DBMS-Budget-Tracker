<?php
session_start();
require 'config.php';
$user_id = $_SESSION['userID'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['categoryName'])) {
    $categoryName = htmlspecialchars(trim($_POST['categoryName']));
    $stmt = $conn->prepare("INSERT INTO categories (userID, categoryName) VALUES (:userID, :categoryName)");
    $stmt->bindParam(':userID', $user_id);
    $stmt->bindParam(':categoryName', $categoryName);
    $stmt->execute();
    $_SESSION['success'] = "Category added!";
}
header("Location: add_expense.php");
exit();
?>
