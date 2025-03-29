<?php
session_start();
include "db.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Error: Unauthorized access";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table_name = filter_var($_POST['table_name'], FILTER_SANITIZE_STRING);

    // Validate table name (only alphanumeric and underscores)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        echo "Error: Invalid table name. Use only letters, numbers, and underscores.";
        exit();
    }

    // Check if table already exists
    $check_table = "SHOW TABLES LIKE '$table_name'";
    $result = $conn->query($check_table);
    if ($result->num_rows > 0) {
        echo "Error: Table '$table_name' already exists.";
        exit();
    }

    // Create SQL for table creation with fixed columns
    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        name VARCHAR(255) NOT NULL,
        pigeon1 TIME DEFAULT '00:00:00',
        pigeon2 TIME DEFAULT '00:00:00',
        pigeon3 TIME DEFAULT '00:00:00',
        pigeon4 TIME DEFAULT '00:00:00',
        pigeon5 TIME DEFAULT '00:00:00',
        pigeon6 TIME DEFAULT '00:00:00',
        pigeon7 TIME DEFAULT '00:00:00',
        total TIME GENERATED ALWAYS AS (
            SEC_TO_TIME(
                TIME_TO_SEC(IFNULL(pigeon1, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon2, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon3, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon4, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon5, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon6, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon7, '00:00:00'))
            )
        ) STORED
    )";

    try {
        // Create the table
        if ($conn->query($sql)) {
            echo "Success: Table '$table_name' created successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Invalid request method";
}
?> 