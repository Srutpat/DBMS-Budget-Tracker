<?php
session_start();
require 'config.php';

$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit();
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = :userID");
$stmt->execute([':userID' => $userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get budget info
$budgetStmt = $conn->prepare("SELECT * FROM budget WHERE userID = :userID");
$budgetStmt->execute([':userID' => $userID]);
$budget = $budgetStmt->fetch(PDO::FETCH_ASSOC);

// Monthly expenses summary
$currentMonth = date('Y-m');
$expensesStmt = $conn->prepare("SELECT SUM(amount) AS totalSpent FROM expenses WHERE userID = :userID AND DATE_FORMAT(dateAdded, '%Y-%m') = :month");
$expensesStmt->execute([':userID' => $userID, ':month' => $currentMonth]);
$monthlyexpenses = $expensesStmt->fetch(PDO::FETCH_ASSOC)['totalSpent'] ?? 0;

// Remaining balance
$remaining = $budget['totalBudget'] - $monthlyexpenses;
$lowBalance = $remaining < $budget['lowBalanceThreshold'];

// Category-wise spending
$categoryStmt = $conn->prepare("
    SELECT categoryID, SUM(amount) as total
    FROM expenses
    WHERE userID = :userID AND DATE_FORMAT(dateAdded, '%Y-%m') = :month
    GROUP BY categoryID
    ORDER BY total DESC
");
$categoryStmt->execute([':userID' => $userID, ':month' => $currentMonth]);
$categoryBreakdown = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Top 3 expenses
$topexpensesStmt = $conn->prepare("
    SELECT description, amount, dateAdded 
    FROM expenses
    WHERE userID = :userID 
    ORDER BY amount DESC 
    LIMIT 3
");
$topexpensesStmt->execute([':userID' => $userID]);
$topexpenses = $topexpensesStmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1f1f1f;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #2c2c2c;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        h2, h3 {
            text-align: center;
            color: #00c6ff;
            margin-top: 30px;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-info img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #00c6ff;
            margin-bottom: 15px;
        }

        .profile-info .name {
            font-size: 1.5rem;
            color: #00c6ff;
            font-weight: bold;
        }

        .profile-info .phone,
        .profile-info .bio {
            font-size: 1.1rem;
            color: #ccc;
        }

        .section-box {
            background-color: #3a3a3a;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .section-box p {
            font-size: 1rem;
            margin: 10px 0;
        }

        table {
            width: 100%;
            color: #fff;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
        }

        table tr:nth-child(even) {
            background-color: #444;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            padding: 10px 0;
            border-bottom: 1px solid #555;
        }

        a.back-link,
        .section-box a {
            color: #00c6ff;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
        }

        a.back-link:hover,
        .section-box a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Profile</h2>
    <div class="profile-info">
        <img src="<?= $user['profileImage'] ? $user['profileImage'] : 'default-avatar.png' ?>" alt="Profile Image">
        <div class="name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="phone"><?= htmlspecialchars($user['phoneNumber']) ?></div>
        <div class="bio"><?= htmlspecialchars($user['bio']) ?></div>
    </div>

    <h2>Budget Overview</h2>
    <div class="section-box">
        <p><strong>Total Budget:</strong> ₹<?= $budget['totalBudget'] ?></p>
        <p><strong>Spent This Month:</strong> ₹<?= $monthlyexpenses ?></p>
        <p><strong>Remaining:</strong> ₹<?= $remaining ?></p>
        <?php if ($lowBalance): ?>
            <p style="color: #ff4d4d;"><strong>⚠ Low Balance Alert!</strong> You've reached your threshold.</p>
        <?php endif; ?>
    </div>

    <h3>This Month's Expenses Breakdown</h3>
    <div class="section-box">
        <?php if ($categoryBreakdown): ?>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoryBreakdown as $cat): ?>
                        <tr>
                            <td>Category <?= htmlspecialchars($cat['categoryID']) ?></td>
                            <td>₹<?= $cat['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No expenses recorded this month.</p>
        <?php endif; ?>
    </div>

    <h3>Top 3 Expenses</h3>
    <div class="section-box">
        <?php if ($topexpenses): ?>
            <ul>
                <?php foreach ($topexpenses as $exp): ?>
                    <li><?= htmlspecialchars($exp['description']) ?> – ₹<?= $exp['amount'] ?> (<?= $exp['dateAdded'] ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No top expenses yet.</p>
        <?php endif; ?>
    </div>

    <div class="section-box" style="text-align: center;">
        <a href="add_expenses.php">➕ Add New Expense</a>
    </div>

    <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
</div>

</body>
</html>
