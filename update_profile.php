<?php
session_start();
require 'config.php';

$userID = $_SESSION['userID'] ?? null;

if ($userID && $_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = $_POST['fullName'];
    $phone = $_POST['phoneNumber'];
    $bio = $_POST['bio'];
    $profileImage = $_FILES['profileImage']['name'];

    $imagePath = '';
    if ($profileImage) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($profileImage);
        move_uploaded_file($_FILES["profileImage"]["tmp_name"], $imagePath);
    }

    // Keep old image if not uploaded
    if (!$imagePath) {
        $stmtOld = $conn->prepare("SELECT profileImage FROM users WHERE userID = :userID");
        $stmtOld->execute([':userID' => $userID]);
        $imagePath = $stmtOld->fetchColumn();
    }

    $stmt = $conn->prepare("UPDATE users SET fullName = :fullName, phoneNumber = :phoneNumber, bio = :bio, profileImage = :profileImage WHERE userID = :userID");
    $stmt->execute([
        ':fullName' => $fullName,
        ':phoneNumber' => $phone,
        ':bio' => $bio,
        ':profileImage' => $imagePath,
        ':userID' => $userID
    ]);

    $_SESSION['success'] = "Profile updated!";
    header("Location: profile.php");
    exit();
}
?>
