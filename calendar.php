<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$selectedMonth = $_GET['month'] ?? date('Y-m');

$startDate = date('Y-m-01', strtotime($selectedMonth));
$endDate = date('Y-m-t', strtotime($selectedMonth));

$stmt = $conn->prepare("SELECT dateAdded, description, amount FROM expenses 
    WHERE budgetID IN (SELECT budgetID FROM budget WHERE userID = ?) 
    AND dateAdded BETWEEN ? AND ?");
$stmt->execute([$userID, $startDate, $endDate]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by date
$dailyExpenses = [];
foreach ($expenses as $exp) {
    $day = date('Y-m-d', strtotime($exp['dateAdded']));
    $dailyExpenses[$day][] = $exp;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Calendar | Budget Tracker</title>
    <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            margin: 0;
            padding: 20px;
        }
        .calendar-container {
            max-width: 1000px;
            margin: auto;
            background: #2a2d34;
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .month-select {
            text-align: center;
            margin-bottom: 20px;
        }
        .month-select input {
            padding: 8px 14px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            outline: none;
            background: #4f5d75;
            color: white;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .day {
            background: #4f5d75;
            padding: 10px;
            border-radius: 10px;
            height: 100px;
            position: relative;
            font-size: 13px;
            overflow-y: auto;
            transition: all 0.2s ease-in-out;
        }
        .day:hover {
            background: #6c7a99;
            transform: scale(1.03);
        }
        .day-number {
            font-weight: bold;
            font-size: 15px;
        }
        .expense {
            margin-top: 4px;
            background: rgba(255,255,255,0.1);
            padding: 4px;
            border-radius: 5px;
        }
        .amount {
            font-weight: bold;
            color: #ffd166;
        }
        .back-btn {
            display: block;
            margin: 30px auto 0;
            background: #ef476f;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            width: fit-content;
            text-decoration: none;
        }
        .day-name {
            text-align: center;
            font-weight: bold;
            color: #dcdcdc;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="month-select">
            <form method="get">
                <label for="month">📅 Select Month: </label>
                <input type="month" name="month" id="month" value="<?= $selectedMonth ?>">
                <button type="submit">Show</button>
            </form>
        </div>

        <div class="calendar">
            <?php
            // Day names row
            $dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            foreach ($dayNames as $dayName) {
                echo "<div class='day-name'>$dayName</div>";
            }

            $firstDayOfMonth = date('w', strtotime($startDate)); // 0–6 (Sun–Sat)
            $daysInMonth = date('t', strtotime($startDate));

            // Empty blocks before 1st day
            for ($i = 0; $i < $firstDayOfMonth; $i++) {
                echo "<div></div>";
            }

            // Print all days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = date('Y-m-d', strtotime("$selectedMonth-$day"));
                $isExpenseDay = isset($dailyExpenses[$date]);

                echo "<div class='day'>";
                echo "<div class='day-number'>$day</div>";

                if ($isExpenseDay) {
                    foreach ($dailyExpenses[$date] as $exp) {
                        $amount = $exp['amount'];
                        $emoji = '';
                        if ($amount <= 100) $emoji = '😌';
                        elseif ($amount <= 500) $emoji = '💸';
                        elseif ($amount <= 1000) $emoji = '🤑';
                        else $emoji = '🔥';

                        echo "<div class='expense'>{$emoji} <span class='amount'>₹" . number_format($amount) . "</span> - " . htmlspecialchars($exp['description']) . "</div>";
                    }
                }

                echo "</div>";
            }
            ?>
        </div>

        <a class="back-btn" href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>
