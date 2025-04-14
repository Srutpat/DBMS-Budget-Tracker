<?php
session_start();
require 'config.php';

$userID = $_SESSION['userID'] ?? null;

if (!$userID) {
    header("Location: login.php");
    exit();
}

// Get current user info
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = :userID");
$stmt->execute([':userID' => $userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <!-- Keep same theme CSS here -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Your Profile</h2>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="form-box">
        <label>Full Name:</label>
        <input type="text" name="fullName" value="<?= htmlspecialchars($user['fullName']) ?>" required>

        <label>Phone Number:</label>
        <input type="text" name="phoneNumber" value="<?= htmlspecialchars($user['phoneNumber']) ?>">

        <label>Bio:</label>
        <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

        <label>Profile Image:</label>
        <input type="file" name="profileImage">
        <?php if ($user['profileImage']): ?>
            <img src="<?= $user['profileImage'] ?>" width="100" style="margin-top:10px;">
        <?php endif; ?>

        <button type="submit">Update Profile</button>
    </form>

    <br><a href="dashboard.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>
