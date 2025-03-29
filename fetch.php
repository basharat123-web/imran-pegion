<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first";
    exit();
}

// Get the table name from the query parameter, default to pigeon_race_results
$table = isset($_GET['table']) ? filter_var($_GET['table'], FILTER_SANITIZE_STRING) : 'pigeon_race_results';

// Validate table name format
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    die("Invalid table name format");
}

// Check if table exists
$check_table = $conn->query("SHOW TABLES LIKE '$table'");
if (!$check_table || $check_table->num_rows === 0) {
    die("Table does not exist");
}

// Check if this is a race results table by looking for required columns
$columns_query = "SHOW COLUMNS FROM `$table`";
$columns_result = $conn->query($columns_query);
$columns = [];
while ($column = $columns_result->fetch_assoc()) {
    $columns[] = $column['Field'];
}
$required_columns = ['id', 'date', 'name', 'pigeon1', 'pigeon2', 'pigeon3', 'pigeon4', 'pigeon5', 'pigeon6', 'pigeon7', 'total'];
$is_race_table = count(array_intersect($required_columns, $columns)) === count($required_columns);

if (!$is_race_table) {
    echo "Invalid table structure";
    exit();
}

// Fetch all records
$sql = "SELECT * FROM $table ORDER BY date DESC, total ASC";
$result = $conn->query($sql);

// Start with the header row
echo "<tr>
        <th>ID</th>
        <th>Date</th>
        <th>Name</th>
        <th>Pigeon 1</th>
        <th>Pigeon 2</th>
        <th>Pigeon 3</th>
        <th>Pigeon 4</th>
        <th>Pigeon 5</th>
        <th>Pigeon 6</th>
        <th>Pigeon 7</th>
        <th>Total</th>";

// Add Actions column for admin users
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    echo "<th>Actions</th>";
}
echo "</tr>";

if ($result->num_rows > 0) {
    $display_id = 1;
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon1"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon2"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon3"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon4"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon5"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon6"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["pigeon7"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["total"]) . "</td>";
        
        // Add edit and delete buttons for admin users
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo "<td>";
            echo "<button class='action-btn edit-btn' onclick='openEditModal(" . 
                 $row["id"] . ", \"" . 
                 addslashes($row["name"]) . "\", \"" . 
                 addslashes($row["date"]) . "\", \"" . 
                 addslashes($row["pigeon1"]) . "\", \"" . 
                 addslashes($row["pigeon2"]) . "\", \"" . 
                 addslashes($row["pigeon3"]) . "\", \"" . 
                 addslashes($row["pigeon4"]) . "\", \"" . 
                 addslashes($row["pigeon5"]) . "\", \"" . 
                 addslashes($row["pigeon6"]) . "\", \"" . 
                 addslashes($row["pigeon7"]) . "\")'>Edit</button>";
            echo "<button class='action-btn delete-btn' onclick='deleteRow(" . $row["id"] . ")'>Delete</button>";
            echo "</td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='12' style='text-align: center;'>No results found</td></tr>";
}

$conn->close();
?>