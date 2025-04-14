<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<h2 style='color: white; text-align: center;'>⚠️ Please log in first to view your expenses! ⚠️</h2>";
    exit;
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

// Fetch expenses from database
try {
    $stmt = $conn->prepare("SELECT category, amount, date FROM expenses WHERE user_id = :user_id ORDER BY date DESC");
    $stmt->execute(['user_id' => $user_id]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching expenses: " . $e->getMessage());
}

// Category emojis mapping
$categoryEmojis = [
    "food" => "🍔",
    "shopping" => "🛍️",
    "stationery" => "✏️",
    "travel" => "🚌",
    "rent" => "🏠",
    "internet" => "📶",
    "fuel" => "⛽",
    "entertainment" => "🎬",
    "others" => "💰"
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="circle-animation"></div>
    <div class="circle-animation"></div>
    <div class="circle-animation"></div>

    <div class="expenses-container">
        <h2 class="glow-text">💸 Your Expenses 💸</h2>

        <div class="expenses-list">
            <?php if (count($expenses) > 0): ?>
                <?php foreach ($expenses as $expense): 
                    $emoji = $categoryEmojis[strtolower($expense['category'])] ?? "💰"; // Default emoji for unknown categories
                ?>
                    <div class="expense-item">
                        <?= $emoji . " " . ucfirst($expense['category']) . ": ₹" . number_format($expense['amount'], 2) ?> 
                        <br><small><?= date("d M Y", strtotime($expense['date'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: white; font-size: 18px;">No expenses recorded yet. Start tracking now! 📊</p>
            <?php endif; ?>
        </div>

        <button class="floating-btn" onclick="location.reload()">🔄 Refresh</button>
    </div>

</body>
</html>
