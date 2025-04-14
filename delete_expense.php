<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_ids'])) {
    $deleteIDs = $_POST['delete_ids'];

    // Build placeholders (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($deleteIDs), '?'));

    // Prepare delete query
    $stmt = $conn->prepare("DELETE FROM expenses WHERE expenseID IN ($placeholders)");
    $stmt->execute($deleteIDs);
}

header("Location: dashboard.php");
exit();
