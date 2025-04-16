<!-- Signup Form HTML -->
<?php
session_start();
require 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phoneNumber']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $message = "⚠️ All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Invalid email format.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "⚠️ Phone number must be 10 digits.";
    } elseif ($password !== $confirm_password) {
        $message = "⚠️ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $message = "⚠️ Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO users (name, email, phoneNumber, password) VALUES (:name, :email, :phone, :password)");
            $insertStmt->bindParam(':name', $name);
            $insertStmt->bindParam(':email', $email);
            $insertStmt->bindParam(':phone', $phone);
            $insertStmt->bindParam(':password', $hashedPassword);

            if ($insertStmt->execute()) {
                $_SESSION['success'] = "🎉 Signup successful. Please login.";
                header("Location: login.php");
                exit();
            } else {
                $message = "❌ Error during registration. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup | Budget Tracker</title>
    <link rel="icon" href="logo.png" type="image/png">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #141E30, #243B55);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
            color: #fff;
            text-align: center;
        }

        .form-container img {
            width: 70px;
            margin-bottom: 10px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #ffd700;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #ffd700;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .action-btn {
            background: linear-gradient(to right, #00b09b, #96c93d);
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            margin-top: 15px;
            font-size: 16px;
            cursor: pointer;
        }

        .action-btn:hover {
            background: linear-gradient(to right, #96c93d, #00b09b);
        }

        .message {
            margin-bottom: 15px;
            color: #ff6b6b;
            font-size: 14px;
        }

        .form-footer {
            margin-top: 20px;
            font-size: 14px;
        }

        .form-footer a {
            color: #ffd700;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="form-container">
    
    <h2>Sign Up</h2>
    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST" action="">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phoneNumber" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="submit" name="signup" value="Sign Up" class="action-btn">
    </form>

    <div class="form-footer">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
