
<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Budget and expense logic
$incomeQuery = $conn->prepare("SELECT totalBudget FROM budget WHERE userID = ?");
$incomeQuery->execute([$userID]);
$income = $incomeQuery->fetchColumn() ?? 0;

$expenseQuery = $conn->prepare("SELECT expenseID, description, amount, dateAdded FROM expenses WHERE budgetID IN (SELECT budgetID FROM budget WHERE userID = ?)");
$expenseQuery->execute([$userID]);
$expenses = $expenseQuery->fetchAll(PDO::FETCH_ASSOC);

$totalExpenses = array_sum(array_column($expenses, 'amount'));
$remainingBalance = $income - $totalExpenses;

$timeQuery = $conn->prepare("SELECT MAX(dateAdded) FROM expenses WHERE budgetID IN (SELECT budgetID FROM budget WHERE userID = ?)");
$timeQuery->execute([$userID]);
$lastUpdated = $timeQuery->fetchColumn();

// Group expenses by month
$monthWiseData = [];
foreach ($expenses as $expense) {
    $month = date("M Y", strtotime($expense['dateAdded']));
    if (!isset($monthWiseData[$month])) {
        $monthWiseData[$month] = 0;
    }
    $monthWiseData[$month] += $expense['amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Budget Tracker</title>
    <link rel="icon" href="logo.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
       * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #1a1a2e, #16213e);
            min-height: 100vh;
            color: #f0f0f0;
        }

        .logout-btn {
            position: fixed;
            top: 20px;
            right: 30px;
            background: #dc3545;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
            z-index: 1000;
        }

        .main-container {
            display: flex;
            flex-wrap: wrap;
            padding: 80px 40px 40px;
            gap: 40px;
            justify-content: center;
        }

        .left-panel, .right-panel {
            background: #2b2f3a;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.3);
        }

        .left-panel {
            flex: 1 1 420px;
            max-width: 600px;
        }

        .right-panel {
            flex: 1 1 350px;
            max-width: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #ffffff;
        }

        .info-box {
            background: #3a3f51;
            border-left: 6px solid #28a745;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            color: white;
            font-size: 16px;
        }

        .top-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .btn {
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            color: #000;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff4e50, #f9d423);
            color: #000;
        }

        .btn-danger:hover {
            opacity: 0.9;
        }

        .expense-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
        }

        .expense-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #444c5e;
            padding: 10px 16px;
            margin-bottom: 10px;
            border-radius: 10px;
            font-size: 15px;
            color: #fff;
        }

        .edit-link {
            font-size: 13px;
            color: #17a2b8;
            text-decoration: none;
            margin-left: 8px;
        }

        .edit-link:hover {
            text-decoration: underline;
        }

        canvas {
            max-width: 100%;
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                padding: 30px 20px;
            }

            .top-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['success'])): ?>
    <p style="text-align:center; background-color: #28a745; color: white; padding: 10px; margin: 10px auto; border-radius: 10px;">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </p>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <p style="text-align:center; background-color: #dc3545; color: white; padding: 10px; margin: 10px auto; border-radius: 10px;">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </p>
<?php endif; ?>

    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

    <div class="main-container">
        <div class="left-panel">
            <h2>Welcome to Spend Wise</h2>

            <div id="incomeForm" style="display:none; margin-bottom: 20px; text-align:center;">
                <form method="post" action="update_income.php">
                    <input type="number" name="new_income" placeholder="Enter new income" step="0.01" required 
                        style="padding:10px; border-radius:6px; width:70%; margin-bottom:10px; border:1px solid #ccc;">
                    <br>
                    <button type="submit" class="btn">Save Income</button>
                </form>
            </div>

            <div class="top-buttons">
                <button class="btn" onclick="window.location.href='add_expense.php'">➕ Add Expense</button>
                <button class="btn" onclick="window.location.href='calendar.php'">📅 View Calendar</button>
                <button class="btn" onclick="toggleIncomeForm()">💰 Update Income</button>
                <button class="btn" onclick="window.location.href='export.php'">⬇️ Download CSV</button>
            </div>

            <div class="info-box">Total Income: ₹<?php echo number_format($income, 2); ?></div>
            <div class="info-box">Total Spent: ₹<?php echo number_format($totalExpenses, 2); ?></div>
            <div class="info-box">Remaining Balance: ₹<?php echo number_format($remainingBalance, 2); ?></div>
            <div class="info-box">Last Added: <?php echo $lastUpdated ? date("d M Y", strtotime($lastUpdated)) : "No expenses added yet."; ?></div>

            <h3 style="margin-top: 20px;">Expenses</h3>

            <?php if (empty($expenses)): ?>
                <p style="text-align:center;">No expenses recorded yet.</p>
            <?php else: ?>
                <form method="post" action="delete_expense.php">
                    <div class="expense-list">
                        <?php foreach ($expenses as $expense): ?>
                            <div class="expense-item">
                                <input type="checkbox" name="delete_ids[]" value="<?php echo $expense['expenseID']; ?>">
                                <span style="flex:1; margin-left: 10px;"><?php echo htmlspecialchars($expense['description']); ?></span>
                                <span>₹<?php echo number_format($expense['amount'], 2); ?></span>
                                <a href="edit_expense.php?id=<?php echo $expense['expenseID']; ?>" class="edit-link">Edit</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 10px;">
                        <button type="submit" class="btn btn-danger">🗑️ Delete Selected</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="right-panel">
            <?php if (!empty($expenses)): ?>
                <div style="width:100%;">
                    <canvas id="expensePieChart" height="250"></canvas>
                    <h4 style="text-align:center; margin: 20px 0 10px;">Monthly Expenses Overview</h4>
                    <canvas id="expenseLineChart" height="250"></canvas>
                </div>
            <?php else: ?>
                <p style="text-align:center; font-style:italic;">No data available to display chart.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleIncomeForm() {
            const form = document.getElementById('incomeForm');
            form.style.display = (form.style.display === "none") ? "block" : "none";
        }

        <?php if (!empty($expenses)): ?>
        // Pie Chart (category-wise or just show total vs remaining)
        const pieCtx = document.getElementById('expensePieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [<?php echo $totalExpenses; ?>, <?php echo $remainingBalance; ?>],
                    backgroundColor: ['#ff6384', '#36a2eb']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Line Chart (monthly trend)
        const lineCtx = document.getElementById('expenseLineChart').getContext('2d');
        const monthLabels = <?php echo json_encode(array_keys($monthWiseData)); ?>;
        const monthValues = <?php echo json_encode(array_values($monthWiseData)); ?>;

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Monthly Expenses',
                    data: monthValues,
                    fill: true,
                    borderColor: '#4bc0c0',
                    backgroundColor: 'rgba(75,192,192,0.2)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
        <?php endif; ?>
    </script>

</body>
</html>

