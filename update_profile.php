<?php
session_start();
require 'config.php';

$userID = $_SESSION['userID'] ?? null;

if ($userID && $_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate user input
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phoneNumber']));
    $bio = htmlspecialchars(trim($_POST['bio']));
    $profileImage = $_FILES['profileImage']['name'];

    // Initialize image path
    $imagePath = '';

    // Handle file upload (only if image is selected)
    if ($profileImage) {
        // Validate image type (only allow jpg, png, gif)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($_FILES['profileImage']['type'], $allowedTypes)) {
            $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed.";
            header("Location: profile.php");
            exit();
        }

        // Generate a unique image name (add timestamp)
        $newImageName = time() . '_' . basename($profileImage);
        $targetDir = "uploads/";
        $imagePath = $targetDir . $newImageName;

        // Move the uploaded image to the target directory
        if (!move_uploaded_file($_FILES["profileImage"]["tmp_name"], $imagePath)) {
            $_SESSION['error'] = "Error uploading the image. Please try again.";
            header("Location: profile.php");
            exit();
        }
    }

    // If no new image uploaded, fetch the old image path from database
    if (!$imagePath) {
        $stmtOld = $conn->prepare("SELECT profileImage FROM users WHERE userID = :userID");
        $stmtOld->execute([':userID' => $userID]);
        $imagePath = $stmtOld->fetchColumn();
    }

    // Update user data in the database
    $stmt = $conn->prepare("UPDATE users SET name = :name, phoneNumber = :phoneNumber, bio = :bio, profileImage = :profileImage WHERE userID = :userID");
    $stmt->execute([
        ':name' => $name,
        ':phoneNumber' => $phone,
        ':bio' => $bio,
        ':profileImage' => $imagePath,
        ':userID' => $userID
    ]);

    // Set success message and redirect to profile page
    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }

        .profile-container h2 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 2rem;
            font-weight: 600;
        }

        .profile-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-container label {
            font-size: 1rem;
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
            align-self: flex-start;
        }

        .profile-container input[type="text"],
        .profile-container textarea,
        .profile-container input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f9f9f9;
            color: #333;
        }

        .profile-image-preview {
            margin: 20px 0;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
            transition: transform 0.3s;
        }

        .profile-image-preview:hover {
            transform: scale(1.1);
        }

        .profile-container button {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            width: 50%;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .profile-container button:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            margin-top: 25px;
            color: #007bff;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
                width: 90%;
            }

            .profile-container h2 {
                font-size: 1.6rem;
            }

            .profile-container button {
                width: 80%;
            }

            .profile-image-preview {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>🧑‍💼 Update Your Profile</h2>
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="phoneNumber">Phone Number:</label>
        <input type="text" name="phoneNumber" id="phoneNumber" value="<?= htmlspecialchars($user['phoneNumber']) ?>">

        <label for="bio">Bio:</label>
        <textarea name="bio" id="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>

        <label for="profileImage">Profile Image:</label>
        <input type="file" name="profileImage" id="profileImage">
        
        <?php if ($user['profileImage']): ?>
            <img src="<?= $user['profileImage'] ?>" class="profile-image-preview">
        <?php endif; ?>

        <button type="submit">Update Profile</button>
    </form>
    <a class="back-link" href="dashboard.php">⬅ Back to Dashboard</a>
</div>

</body>
</html>
