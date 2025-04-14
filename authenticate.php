<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if fields are empty
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Both fields are required.";
        header("Location: login.php");
        exit();
    }

    try {
        // Prepare and execute the SQL query
        $stmt = $conn->prepare("SELECT userID, name, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['userName'] = $user['name'];
            
            header("Location: income.php"); // Redirect to dashboard
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: login.php");
        exit();
    }
}
?>
