-- Create the database
CREATE DATABASE budget_tracker;

-- Use the created database
USE budget_tracker;

-- Create the users table (renamed from [user] to avoid reserved word issue)
-- Create the database (Only needed if not created)
CREATE DATABASE IF NOT EXISTS budget_tracker;
USE budget_tracker;

-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Create the budget table
CREATE TABLE IF NOT EXISTS budget (
    budgetID INT AUTO_INCREMENT PRIMARY KEY, 
    userID INT NOT NULL,
    totalBudget DECIMAL(10,2) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE
);

-- Create the expenses table
CREATE TABLE IF NOT EXISTS expenses (
    expenseID INT AUTO_INCREMENT PRIMARY KEY, 
    budgetID INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budgetID) REFERENCES budget(budgetID) ON DELETE CASCADE
);

