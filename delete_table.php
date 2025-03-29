<?php
session_start();
include "db.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Error: Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_name'])) {
    $table_name = filter_var($_POST['table_name'], FILTER_SANITIZE_STRING);
    
    // Validate table name format (only allow alphanumeric and underscore)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        die("Error: Invalid table name format");
    }
    
    // Check if table exists
    $check_table = $conn->query("SHOW TABLES LIKE '$table_name'");
    if (!$check_table || $check_table->num_rows === 0) {
        die("Error: Table does not exist");
    }
    
    // Check if it's not the home table
    $home_table_result = $conn->query("SELECT value FROM settings WHERE name = 'home_table'");
    if ($home_table_result && $home_table_result->num_rows > 0) {
        $home_table = $home_table_result->fetch_assoc()['value'];
        if ($table_name === $home_table) {
            die("Error: Cannot delete the home table. Please set another table as home table first.");
        }
    }
    
    // Check if table starts with 'pigeon'
    if (strpos($table_name, 'pigeon') !== 0) {
        die("Error: Can only delete tables that start with 'pigeon'");
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Drop the table
        $drop_query = "DROP TABLE IF EXISTS `$table_name`";
        if (!$conn->query($drop_query)) {
            throw new Exception("Failed to delete table: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        echo "Success: Table deleted successfully";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    die("Error: Invalid request method");
}

$conn->close();
?> 