<?php
session_start();
require 'config.php';

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=budget_report.csv");

// Optional: add UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

// Prepare query
$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT description, amount, dateAdded 
    FROM expenses 
    WHERE budgetID IN (
        SELECT budgetID FROM budget WHERE userID = ?
    )
");
$stmt->execute([$userID]);

// CSV Output
$output = fopen("php://output", "w");
fputcsv($output, array("Description", "Amount", "Date")); // Header

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Format date in a way Excel can read properly
    $formattedDate = date("d-m-Y", strtotime($row['dateAdded']));
    fputcsv($output, array(
        $row['description'],
        $row['amount'],
        $formattedDate
    ));
}

fclose($output);
exit();
?>
