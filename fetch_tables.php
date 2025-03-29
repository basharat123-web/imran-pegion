<?php
session_start();
include "db.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");

while ($row = $result->fetch_array()) {
    $table_name = $row[0];
    
    // Check if table has the required columns (to ensure it's a race results table)
    $columns_query = "SHOW COLUMNS FROM `$table_name`";
    $columns_result = $conn->query($columns_query);
    $columns = [];
    while ($column = $columns_result->fetch_assoc()) {
        $columns[] = $column['Field'];
    }

    // Check if this table has the required columns for race results
    $required_columns = ['id', 'date', 'name', 'pigeon1', 'pigeon2', 'pigeon3', 'pigeon4', 'pigeon5', 'pigeon6', 'pigeon7', 'total'];
    $is_race_table = count(array_intersect($required_columns, $columns)) === count($required_columns);

    if ($is_race_table) {
        $tables[] = $table_name;
    }
}

echo json_encode($tables);
?> 