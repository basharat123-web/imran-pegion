<?php
session_start();
include "db.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Error: Unauthorized access.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);

    if (!$user_id || !$new_password) {
        echo "Error: Invalid input. User ID or password is missing.";
        exit();
    }

    // Verify that the user_id exists in the users table
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

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            throw new Exception("Error: Failed to hash the new password.");
        }

        // Update the user's password in the users table
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing UPDATE statement: " . $conn->error);
        }

        $stmt->bind_param("si", $hashed_password, $user_id);

        if (!$stmt->execute()) {
            throw new Exception("Error executing UPDATE statement: " . $stmt->error);
        }

        // Check if the update affected any rows
        if ($stmt->affected_rows === 0) {
            throw new Exception("Error: No rows updated. User ID $user_id may not exist.");
        }

        // Log the password reset in the history table
        $admin_id = $_SESSION['user_id'];
        $log_stmt = $conn->prepare("INSERT INTO password_reset_history (user_id, new_password, reset_by) VALUES (?, ?, ?)");
        if (!$log_stmt) {
            throw new Exception("Error preparing INSERT statement for password reset history: " . $conn->error);
        }

        $log_stmt->bind_param("isi", $user_id, $new_password, $admin_id);

        if (!$log_stmt->execute()) {
            throw new Exception("Error executing INSERT statement for password reset history: " . $log_stmt->error);
        }

        // Commit the transaction
        $conn->commit();
        echo "Success";

        $stmt->close();
        $log_stmt->close();
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
} else {
    echo "Error: Invalid request method.";
}
?>