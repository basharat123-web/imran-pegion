<?php
session_start();
include "db.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Error: Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
    $table = filter_var($_POST['table'], FILTER_SANITIZE_STRING);
    
    // Validate table name format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        die("Error: Invalid table name format");
    }
    
    // Check if table exists
    $check_table = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$check_table || $check_table->num_rows === 0) {
        die("Error: Table does not exist");
    }

    // Validate and process pigeon times
    $pigeons = array();
    for ($i = 1; $i <= 7; $i++) {
        $time = isset($_POST["pigeon$i"]) ? trim($_POST["pigeon$i"]) : '00:00:00';
        if (empty($time)) $time = '00:00:00';
        
        // Validate time format
        if (!preg_match('/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/', $time)) {
            die("Error: Invalid time format for Pigeon $i");
        }
        $pigeons[] = $time;
    }

    // Calculate total (sum of non-zero times)
    $total = 0;
    foreach ($pigeons as $time) {
        if ($time !== '00:00:00') {
            list($hours, $minutes, $seconds) = explode(':', $time);
            $total += $hours * 3600 + $minutes * 60 + $seconds;
        }
    }

    // Format total back to HH:MM:SS
    $hours = floor($total / 3600);
    $minutes = floor(($total % 3600) / 60);
    $seconds = $total % 60;
    $total_formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

    // Update the record
    $stmt = $conn->prepare("UPDATE $table SET 
        name = ?, 
        date = ?,
        pigeon1 = ?, 
        pigeon2 = ?, 
        pigeon3 = ?, 
        pigeon4 = ?, 
        pigeon5 = ?, 
        pigeon6 = ?, 
        pigeon7 = ?,
        total = ?
        WHERE id = ?");
    
    $stmt->bind_param("ssssssssssi", 
        $name, 
        $date,
        $pigeons[0], 
        $pigeons[1], 
        $pigeons[2], 
        $pigeons[3], 
        $pigeons[4], 
        $pigeons[5], 
        $pigeons[6],
        $total_formatted,
        $id
    );

    if ($stmt->execute()) {
        echo "Success: Record updated successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Invalid request method";
}

$conn->close();
?>