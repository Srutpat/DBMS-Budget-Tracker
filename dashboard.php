<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID']; // Get userID from session

// Fetch total budget for the user
$incomeQuery = $conn->prepare("SELECT totalBudget FROM budget WHERE userID = ?");
$incomeQuery->execute([$userID]);
$income = $incomeQuery->fetchColumn() ?? 0; // Default to 0 if no budget exists

// Fetch all expenses for the user
$expenseQuery = $conn->prepare("SELECT expenseID, description, amount, dateAdded, categoryID FROM expenses WHERE budgetID IN (SELECT budgetID FROM budget WHERE userID = ?)");
$expenseQuery->execute([$userID]);
$expenses = $expenseQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories and create a mapping
$categoryQuery = $conn->prepare("SELECT categoryID, categoryName FROM categories WHERE userID = ?");
$categoryQuery->execute([$userID]);
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Create a category mapping (categoryID => categoryName)
$categoryMapping = [];
foreach ($categories as $category) {
    $categoryMapping[$category['categoryID']] = $category['categoryName'];
}

// Initialize an empty array to hold the categorized expenses
$categoryExpenses = [];

// Loop through all expenses and categorize them
foreach ($expenses as $expense) {
    // Check if 'categoryID' exists in the current expense
    if (isset($expense['categoryID'])) {
        // Map categoryID to categoryName
        $catName = $categoryMapping[$expense['categoryID']] ?? 'Uncategorized'; // Default to 'Uncategorized' if not found

        // If this category hasn't been seen before, initialize it with 0
        if (!isset($categoryExpenses[$catName])) {
            $categoryExpenses[$catName] = 0;
        }

        // Add the expense amount to the appropriate category
        $categoryExpenses[$catName] += $expense['amount'];
    } else {
        // Handle cases where categoryID is missing
        echo "Warning: categoryID missing in expense data<br>";
    }
}

// Remove debug output for category-wise expenses
// echo '<pre>';
// print_r($categoryExpenses);
// echo '</pre>';

// Calculate the total expenses
$totalExpenses = array_sum(array_column($expenses, 'amount'));

// Calculate the remaining balance
$remainingBalance = $income - $totalExpenses;

// Fetch the last expense update time
$timeQuery = $conn->prepare("SELECT MAX(dateAdded) FROM expenses WHERE budgetID IN (SELECT budgetID FROM budget WHERE userID = ?)");
$timeQuery->execute([$userID]);
$lastUpdated = $timeQuery->fetchColumn();

// Group expenses by month
$monthWiseData = [];
foreach ($expenses as $expense) {
    // Get the month and year from the expense date
    $month = date("M Y", strtotime($expense['dateAdded']));
    
    // Initialize the month category if it doesn't exist
    if (!isset($monthWiseData[$month])) {
        $monthWiseData[$month] = 0;
    }

    // Add the expense amount to the corresponding month
    $monthWiseData[$month] += $expense['amount'];
}

// Remove debug output for month-wise data
// echo '<pre>';
// print_r($monthWiseData);
// echo '</pre>';

// Example output for remaining balance
// echo "Total Budget: ₹$income<br>";
// echo "Total Expenses: ₹$totalExpenses<br>";
// echo "Remaining Balance: ₹$remainingBalance<br>";
// echo "Last Expense Update: $lastUpdated<br>";
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

        .profile-btn {
            position: fixed;
            top: 20px;
            right: 140px;
            background:rgb(175, 221, 210);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 35%;
            font-size: 16px;
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
    <!-- <button class="profile-btn" onclick="window.location.href='profile.php'">👤</button> -->

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
    <div style="width: 100%;">
        <h3>Spending Overview</h3>
        <canvas id="categoryChart"></canvas>
        <br><br>
        <h3>Monthly Spending</h3>
        <canvas id="monthlyChart"></canvas>
    </div>
</div>


   
</div>

<script>
    window.onload = function() {
        const categoryLabels = <?php echo json_encode(array_keys($categoryExpenses)); ?>;
        const categoryData = <?php echo json_encode(array_values($categoryExpenses)); ?>;

        const ctx1 = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Expenses by Category',
                    data: categoryData,
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'
                    ],
                    borderColor: '#1a1a2e',
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });

        const monthlyLabels = <?php echo json_encode(array_keys($monthWiseData)); ?>;
        const monthlyData = <?php echo json_encode(array_values($monthWiseData)); ?>;

        const ctx2 = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Expenses',
                    data: monthlyData,
                    backgroundColor: '#00c9ff',
                    borderRadius: 10
                }]
            },
            options: {
                scales: {
                    x: {
                        ticks: { color: 'white' }
                    },
                    y: {
                        ticks: { color: 'white' }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });
    }
</script>




</body>
</html>
