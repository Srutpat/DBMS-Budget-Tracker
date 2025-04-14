<?php 
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $message = "Error: Could not register user.";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
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
        <h2>Signup</h2>

        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="Enter Name" required>
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="text" name="contactNo" placeholder="Enter Contact No" required>
            <button type="submit" class="action-btn">Register</button>
        </form>

        <button class="action-btn back-btn" onclick="window.location.href='login.php'">Go to Login</button>
    </div>
</body>
</html>
