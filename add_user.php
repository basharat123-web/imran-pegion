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
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    if (!$username || !$password || !$name || !$role || !in_array($role, ['client', 'admin'])) {
        echo "Error: Invalid input. All fields are required, and role must be 'client' or 'admin'.";
        exit();
    }

    // Check if the username already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$check_stmt) {
        echo "Error preparing username check statement: " . $conn->error;
        exit();
    }
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "Error: Username '$username' already exists.";
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            throw new Exception("Error: Failed to hash the password.");
        }

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing INSERT statement: " . $conn->error);
        }

        $stmt->bind_param("ssss", $username, $hashed_password, $name, $role);

        if (!$stmt->execute()) {
            throw new Exception("Error executing INSERT statement: " . $stmt->error);
        }

        // Get the new user's ID
        $new_user_id = $conn->insert_id;

        // Log the initial password in password_reset_history
        $admin_id = $_SESSION['user_id'];
        $log_stmt = $conn->prepare("INSERT INTO password_reset_history (user_id, new_password, reset_by) VALUES (?, ?, ?)");
        if (!$log_stmt) {
            throw new Exception("Error preparing INSERT statement for password reset history: " . $conn->error);
        }

        $log_stmt->bind_param("isi", $new_user_id, $password, $admin_id);

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