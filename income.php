<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = "";

// Check if user already has an income entry
$checkIncome = $conn->prepare("SELECT * FROM budget WHERE userID = ?");
$checkIncome->execute([$userID]);

// Redirect to dashboard if income already exists and request is not POST
if ($checkIncome->rowCount() > 0 && $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $monthly_income = $_POST['monthly_income'];
    $low_balance = $_POST['low_balance'];

    if ($checkIncome->rowCount() > 0) {
        $updateIncome = $conn->prepare("UPDATE budget SET totalBudget = ?, lowBalanceThreshold = ? WHERE userID = ?");
        if ($updateIncome->execute([$monthly_income, $low_balance, $userID])) {
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Error updating income.";
        }
    } else {
        $insertIncome = $conn->prepare("INSERT INTO budget (userID, totalBudget, lowBalanceThreshold) VALUES (?, ?, ?)");
        if ($insertIncome->execute([$userID, $monthly_income, $low_balance])) {
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Error adding income.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Monthly Income | Budget Tracker</title>
    <!-- <title>Budget Tracker | Manage Smarter</title> -->
    <link rel="icon" href="logo.png" type="image/png">
    <style>
        * {
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #141E30, #243B55);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .form-container {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            padding: 30px 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            text-align: center;
            backdrop-filter: blur(10px);
            animation: slideIn 0.7s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            margin-bottom: 20px;
            color: #ffd700;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #4ca1af;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: #ffd700;
            background-color: rgba(255, 255, 255, 0.15);
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .action-btn {
            background: linear-gradient(45deg, #00b09b, #96c93d);
            color: white;
            font-weight: bold;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .action-btn:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #96c93d, #00b09b);
        }

        .message {
            margin-bottom: 10px;
            color: #ff6b6b;
        }

        .back-btn {
            margin-top: 10px;
            background: transparent;
            border: 1px solid #ffd700;
            color: #ffd700;
        }

        .back-btn:hover {
            background-color: #ffd700;
            color: black;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Set Your Monthly Income</h2>

        <?php if ($message): ?>
            <p class="message"><?= $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="income.php">
            <input type="number" name="monthly_income" placeholder="Enter Monthly Income (₹)" required>
            <input type="number" name="low_balance" placeholder="Low Balance Threshold (₹)" required>
            <button type="submit" class="action-btn">Save Income</button>
        </form>

        <button class="action-btn back-btn" onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
    </div>
</body>
</html>
