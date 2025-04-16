<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userID'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $categoryName = htmlspecialchars(trim($_POST['categoryName'] ?? ''));

    if (!empty($categoryName)) {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (userID, categoryName) VALUES (:userID, :categoryName)");
            $stmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':categoryName', $categoryName, PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['success'] = "Category '$categoryName' added successfully.";
            header("Location: add_expense.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "PDO Error: " . $e->getMessage(); // for now show actual error
            header("Location: add_category.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Category name cannot be empty.";
        header("Location: add_category.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category | Budget Tracker</title>
    <link rel="icon" href="logo.png" type="image/png">
    <style>
        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #141E30, #243B55); height: 100vh; display: flex; align-items: center; justify-content: center; color: #ffffff; }
        .form-container { background: rgba(0, 0, 0, 0.3); border-radius: 16px; padding: 30px 40px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); text-align: center; backdrop-filter: blur(10px); animation: slideIn 0.7s ease-out forwards; width: 100%; max-width: 420px; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
        h2 { margin-bottom: 20px; color: #ffd700; }
        input, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #4ca1af; border-radius: 8px; background-color: rgba(255, 255, 255, 0.1); color: #fff; font-size: 16px; }
        input:focus { outline: none; border-color: #ffd700; background-color: rgba(255, 255, 255, 0.15); }
        .action-btn { background: linear-gradient(45deg, #00b09b, #96c93d); color: white; font-weight: bold; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; margin-top: 15px; font-size: 16px; transition: transform 0.2s ease; }
        .action-btn:hover { transform: scale(1.05); background: linear-gradient(45deg, #96c93d, #00b09b); }
        .message { margin-bottom: 10px; color: #ff6b6b; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Category</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="add_category.php">
            <input type="text" name="categoryName" placeholder="Enter Category Name" required>
            <button type="submit" class="action-btn">Add Category</button>
        </form>
    </div>
</body>
</html>
