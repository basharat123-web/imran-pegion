<?php
// Database connection
include "db.php";

// Step 1: Renumber the ID column before displaying the table
$renumber_query = "
    SET @row_number = 0;
    UPDATE pigeon_race_results
    SET id = (@row_number := @row_number + 1)
    ORDER BY name;
";
if ($conn->multi_query($renumber_query)) {
    // Consume the results of the multi-query to avoid "Commands out of sync" error
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
} else {
    echo "Error renumbering IDs: " . $conn->error;
}

// Step 2: Reset the AUTO_INCREMENT counter to the next value after the highest ID
$max_id_query = "SELECT MAX(id) + 1 AS next_id FROM pigeon_race_results";
$max_id_result = $conn->query($max_id_query);
$next_id = $max_id_result->fetch_assoc()['next_id'];

if ($next_id) {
    $reset_auto_increment_query = "ALTER TABLE pigeon_race_results AUTO_INCREMENT = $next_id";
    if ($conn->query($reset_auto_increment_query) === FALSE) {
        echo "Error resetting AUTO_INCREMENT: " . $conn->error;
    }
}

// Step 3: Calculate the TOTAL column
$total_query = "
    UPDATE pigeon_race_results
    SET total = SEC_TO_TIME(
        TIME_TO_SEC(pigeon1) +
        TIME_TO_SEC(pigeon2) +
        TIME_TO_SEC(pigeon3) +
        TIME_TO_SEC(pigeon4) +
        TIME_TO_SEC(pigeon5) +
        TIME_TO_SEC(pigeon6) +
        TIME_TO_SEC(pigeon7)
    );
";
if ($conn->query($total_query) === FALSE) {
    echo "Error calculating TOTAL: " . $conn->error;
}

// Step 4: Fetch the data to display
$select_query = "SELECT id, name, pigeon1, pigeon2, pigeon3, pigeon4, pigeon5, pigeon6, pigeon7, total FROM pigeon_race_results ORDER BY name";
$result = $conn->query($select_query);

// Display the table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pigeon Race Results</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@600&display=swap" rel="stylesheet">
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: 'Poppins', sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #2ecc71; /* Green header like in your image */
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        h2 {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <h2>Pigeon Race Results</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Pigeon 1</th>
                <th>Pigeon 2</th>
                <th>Pigeon 3</th>
                <th>Pigeon 4</th>
                <th>Pigeon 5</th>
                <th>Pigeon 6</th>
                <th>Pigeon 7</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['pigeon1'] . "</td>";
                    echo "<td>" . $row['pigeon2'] . "</td>";
                    echo "<td>" . $row['pigeon3'] . "</td>";
                    echo "<td>" . $row['pigeon4'] . "</td>";
                    echo "<td>" . $row['pigeon5'] . "</td>";
                    echo "<td>" . $row['pigeon6'] . "</td>";
                    echo "<td>" . $row['pigeon7'] . "</td>";
                    echo "<td>" . $row['total'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No data found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
// Close the connection
$conn->close();
?>