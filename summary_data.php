<?php
session_start();
include "db.php";

// Get the table name from the query parameter, default to pigeon_race_results
$table = isset($_GET['table']) ? filter_var($_GET['table'], FILTER_SANITIZE_STRING) : 'pigeon_race_results';

// Validate table name format
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo json_encode(['error' => 'Invalid table name format']);
    exit();
}

// Check if table exists
$check_table = $conn->query("SHOW TABLES LIKE '$table'");
if (!$check_table || $check_table->num_rows === 0) {
    echo json_encode(['error' => 'Table does not exist']);
    exit();
}

try {
    // Calculate total pigeons (7 pigeons per row * number of rows)
    $total_query = "SELECT COUNT(*) * 7 as total FROM $table";
    $total_result = $conn->query($total_query);
    $total_pigeons = $total_result->fetch_assoc()['total'];

    // Calculate flown pigeons (count of non-zero times)
    $flown_query = "SELECT COUNT(*) as flown FROM (
        SELECT pigeon1 as time FROM $table WHERE pigeon1 != '00:00:00'
        UNION ALL
        SELECT pigeon2 FROM $table WHERE pigeon2 != '00:00:00'
        UNION ALL
        SELECT pigeon3 FROM $table WHERE pigeon3 != '00:00:00'
        UNION ALL
        SELECT pigeon4 FROM $table WHERE pigeon4 != '00:00:00'
        UNION ALL
        SELECT pigeon5 FROM $table WHERE pigeon5 != '00:00:00'
        UNION ALL
        SELECT pigeon6 FROM $table WHERE pigeon6 != '00:00:00'
        UNION ALL
        SELECT pigeon7 FROM $table WHERE pigeon7 != '00:00:00'
    ) as flown_pigeons";
    
    $flown_result = $conn->query($flown_query);
    $flown_pigeons = $flown_result->fetch_assoc()['flown'];

    // Calculate remaining pigeons
    $remaining_pigeons = $total_pigeons - $flown_pigeons;

    // Return the data as JSON
    echo json_encode([
        'totalPigeons' => $total_pigeons,
        'flownPigeons' => $flown_pigeons,
        'remainingPigeons' => $remaining_pigeons
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>