<?php
session_start();
include "db.php"; // Include database connection

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Get the logged-in user ID
    $date = $_POST['date'];

    if (empty($date)) {
        echo "Date is required!";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO dates (user_id, selected_date) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $date);

    if ($stmt->execute()) {
        echo "Date saved successfully!";
    } else {
        echo "Error saving date.";
    }
}
?>
