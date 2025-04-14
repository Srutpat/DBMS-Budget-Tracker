<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$newIncome = $_POST['new_income'] ?? 0;

if ($newIncome > 0) {
    // Check if a budget entry already exists for this user
    $checkQuery = $conn->prepare("SELECT budgetID FROM budget WHERE userID = ?");
    $checkQuery->execute([$userID]);
    $existingBudget = $checkQuery->fetchColumn();

    if ($existingBudget) {
        // Update existing budget
        $updateQuery = $conn->prepare("UPDATE budget SET totalBudget = ? WHERE userID = ?");
        $updateQuery->execute([$newIncome, $userID]);
    } else {
        // Insert new budget row
        $insertQuery = $conn->prepare("INSERT INTO budget (userID, totalBudget) VALUES (?, ?)");
        $insertQuery->execute([$userID, $newIncome]);
    }

    $_SESSION['success'] = "Income updated successfully.";
} else {
    $_SESSION['error'] = "Invalid income entered.";
}

header("Location: dashboard.php");
exit();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Income | Budget Tracker</title>
    <link rel="icon" href="logo.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #1c1e26;
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #2b2f3a;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        input[type="number"] {
            padding: 10px;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 16px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            color: #17a2b8;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Update Income</h2>
        <form method="POST">
            <label for="totalBudget">Enter new total income:</label>
            <input type="number" step="0.01" name="totalBudget" id="totalBudget" value="<?php echo htmlspecialchars($currentIncome); ?>" required>
            <button type="submit">Update</button>
        </form>
        <div class="back-link">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
