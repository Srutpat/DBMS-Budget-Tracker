<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$expenseID = $_GET['id'] ?? null;

if (!$expenseID) {
    header("Location: dashboard.php");
    exit();
}

// Fetch data
$stmt = $conn->prepare("SELECT * FROM expenses WHERE expenseID = ?");
$stmt->execute([$expenseID]);
$expense = $stmt->fetch();

if (!$expense) {
    echo "Expense not found!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $desc = $_POST['description'];
    $amt = $_POST['amt'];

    $update = $conn->prepare("UPDATE expenses SET description = ?, amount = ? WHERE expenseID = ?");
    $update->execute([$desc, $amt, $expenseID]);

    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Expense | Budget Tracker</title>
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
            width: 100%;
            max-width: 420px;
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
    <h2>Edit Expense</h2>
    <form method="post">
        <input type="text" name="description" placeholder="Description" required value="<?php echo htmlspecialchars($expense['description']); ?>">
        <input type="number" name="amt" step="0.01" placeholder="Amount" required value="<?php echo $expense['amount']; ?>">
        <button type="submit" class="action-btn">Save Changes</button>
    </form>
    <button class="action-btn back-btn" onclick="window.location.href='dashboard.php'">← Back to Dashboard</button>
</div>

</body>
</html>
