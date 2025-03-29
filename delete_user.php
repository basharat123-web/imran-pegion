<?php
session_start();
include "db.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Error: Unauthorized access.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize the user_id
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    if (!$user_id) {
        echo "Error: Invalid user ID.";
        exit();
    }

    // Prevent the admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        echo "Error: You cannot delete your own account.";
        exit();
    }

    // Verify that the user exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    if (!$check_stmt) {
        echo "Error preparing user check statement: " . $conn->error;
        exit();
    }
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        echo "Error: User with ID $user_id does not exist.";
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Delete the user (cascading deletes will handle related records)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if (!$stmt) {
        echo "Error preparing DELETE statement: " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Error: Invalid request method.";
}
?>