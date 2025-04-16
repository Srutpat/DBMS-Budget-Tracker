<?php
session_start();

// Logging function
function log_debug($message) {
    $logFile = __DIR__ . '/debug_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// PHPMailer
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'config.php'; // Assumes $conn is PDO

$user_id = $_SESSION['userID'] ?? null;
$user_email = '';
$budget_id = null;

log_debug("Session userID: " . ($user_id ?? 'Not set'));

// Fetch user email and budgetID
if ($user_id) {
    try {
        $stmt = $conn->prepare("SELECT email FROM users WHERE userID = :userID");
        $stmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user_email = $stmt->fetchColumn();

        log_debug("Fetched email: $user_email");

        $stmt = $conn->prepare("SELECT budgetID FROM budget WHERE userID = :userID");
        $stmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $budget_id = $stmt->fetchColumn();

        log_debug("Fetched budgetID: " . ($budget_id ?? 'Not found'));
    } catch (PDOException $e) {
        log_debug("PDO Error while fetching email/budgetID: " . $e->getMessage());
    }
} else {
    log_debug("No userID found in session");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $amount = floatval($_POST['amount'] ?? 0);
    $dateAdded = $_POST['dateAdded'] ?? date('Y-m-d');
    $category_id = !empty($_POST['categoryID']) ? intval($_POST['categoryID']) : null;

    if ($user_id && $budget_id && !empty($description) && $amount > 0) {
        try {
            // ✅ Fix: Proper SQL with VALUES
            $stmt = $conn->prepare("INSERT INTO expenses (userID, budgetID, description, amount, dateAdded, categoryID)
                VALUES (:userID, :budgetID, :description, :amount, :dateAdded, :categoryID)");

            $stmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':budgetID', $budget_id, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':dateAdded', $dateAdded);
            $stmt->bindParam(':categoryID', $category_id, PDO::PARAM_INT);

            $stmt->execute();

            $_SESSION['success'] = "Expense added successfully.";

            // Send email notification
            if (!empty($user_email)) {
                $mail = new PHPMailer(true);
                try {
                    log_debug("Configuring SMTP for $user_email...");
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'budgettracker27@gmail.com';
                    $mail->Password   = 'fnco fdub nzsj zxye'; // App password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('budgettracker27@gmail.com', 'Budget Tracker');
                    $mail->addAddress($user_email);

                    $mail->isHTML(true);
                    $mail->Subject = 'New Expense Added';
                    $mail->Body    = "<h3>Hello,</h3>
                        <p>Your new expense <strong>'$description'</strong> of ₹<strong>$amount</strong> on <strong>$dateAdded</strong> has been recorded.</p>
                        <p style='color:green;'>Keep tracking and saving!</p>
                        <br><p>- Budget Tracker</p>";

                    $mail->send();
                    log_debug("Email successfully sent to $user_email.");
                    $_SESSION['success'] .= " Email sent.";

                    // Budget check
                    $stmt = $conn->prepare("SELECT totalBudget, lowBalanceThreshold FROM budget WHERE budgetID = :budgetID");
                    $stmt->bindParam(':budgetID', $budget_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $budgetData = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($budgetData) {
                        $totalBudget = floatval($budgetData['totalBudget']);
                        $lowThreshold = floatval($budgetData['lowBalanceThreshold']);

                        $stmt = $conn->prepare("SELECT SUM(amount) FROM expenses WHERE budgetID = :budgetID");
                        $stmt->bindParam(':budgetID', $budget_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $totalSpent = floatval($stmt->fetchColumn() ?? 0);

                        $remainingBalance = $totalBudget - $totalSpent;
                        log_debug("Budget Check - Total: ₹$totalBudget, Spent: ₹$totalSpent, Remaining: ₹$remainingBalance, Threshold: ₹$lowThreshold");

                        if ($remainingBalance < $lowThreshold) {
                            $mail2 = new PHPMailer(true);
                            $mail2->isSMTP();
                            $mail2->Host       = 'smtp.gmail.com';
                            $mail2->SMTPAuth   = true;
                            $mail2->Username   = 'budgettracker27@gmail.com';
                            $mail2->Password   = 'fnco fdub nzsj zxye';
                            $mail2->SMTPSecure = 'tls';
                            $mail2->Port       = 587;

                            $mail2->setFrom('budgettracker27@gmail.com', 'Budget Tracker');
                            $mail2->addAddress($user_email);

                            $mail2->isHTML(true);
                            $mail2->Subject = 'Low Balance Alert';
                            $mail2->Body    = "<h3>Heads up!</h3>
                                <p>Your remaining balance is ₹<strong>$remainingBalance</strong>, which is below your threshold of ₹<strong>$lowThreshold</strong>.</p>
                                <p style='color:red;'>Please review your expenses and adjust accordingly.</p>
                                <br><p>- Budget Tracker</p>";

                            $mail2->send();
                            log_debug("Low balance warning email sent to $user_email.");
                            $_SESSION['success'] .= " Low balance alert sent.";
                        }
                    }

                } catch (Exception $e) {
                    log_debug("Mailer Error: " . $mail->ErrorInfo);
                    $_SESSION['success'] .= " But email could not be sent.";
                }
            } else {
                log_debug("No email found for user.");
                $_SESSION['success'] .= " But no email found.";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to add expense.";
            log_debug("PDO Insert Error: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "Invalid input. Ensure all fields are filled correctly.";
        log_debug("Validation failed: userID=$user_id, budgetID=$budget_id, description=$description, amount=$amount");
    }

    header("Location: add_expense.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Expense | Budget Tracker</title>
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
            width: 450px;
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

        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #4ca1af;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 16px;
        }

        select {
            background-color: #2f3b52;
            color: white;
        }

        select option {
            background-color: #2f3b52;
            color: white;
        }

        input:focus, select:focus {
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
            width: 48%;
            margin-top: 15px;
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .action-btn:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #96c93d, #00b09b);
        }

        .back-btn {
    margin-top: 20px;
    background: transparent;
    border: 1px solid #ffd700;
    color: #ffd700;
    height: 40px;
    width: 100%;
}


        .back-btn:hover {
            background-color: #ffd700;
            color: black;
        }

        .message {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .error-message {
            color: #ff6b6b;
        }

        .success-message {
            color: #66ff99;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        .back-btn-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Add New Expense</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="message error-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <p class="message success-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form method="POST" action="add_expense.php">
            <input type="text" name="description" placeholder="Expense Description" required>
            <input type="number" step="0.01" name="amount" placeholder="Amount (₹)" required>
            <input type="date" name="dateAdded" value="<?= date('Y-m-d') ?>" required>

            <select name="categoryID" id="category" required>
                <option value="">-- Select Category --</option>
                <?php
                    $catStmt = $conn->prepare("SELECT categoryID, categoryName FROM categories WHERE userID = :userID");
                    $catStmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
                    $catStmt->execute();
                    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($categories as $cat) {
                        echo "<option value='{$cat['categoryID']}'>{$cat['categoryName']}</option>";
                    }
                ?>
            </select>

            <!-- Moved Add Expense button here -->
            <div class="button-container">
                <button type="submit" class="action-btn">Add Expense</button>
                <button type="button" class="action-btn" onclick="window.location.href='add_category.php'">Add New Category</button>
            </div>
        </form>

        <!-- Go to Dashboard button below the row -->
        <div class="back-btn-container">
            <button class="back-btn" onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
        </div>
    </div>

</body>
</html>
