<?php
include "db.php";

// Get the table name from URL parameter
$table_name = isset($_GET['table']) ? filter_var($_GET['table'], FILTER_SANITIZE_STRING) : 'pigeon_race_results';

// Validate table name
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
    die("Invalid table name");
}

// Check if table exists
$check_table = $conn->query("SHOW TABLES LIKE '$table_name'");
if ($check_table->num_rows === 0) {
    die("Table does not exist");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Table - <?php echo ucfirst($table_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            color: #333;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .navbar {
            background-color: #333;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 4px;
        }
        .navbar a:hover {
            background-color: #4CAF50;
        }
        .navbar a.active {
            background-color: #4CAF50;
        }
        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }
            th, td {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h1><?php echo ucfirst($table_name); ?> Race Results</h1>
        <div class="table-container">
            <table>
                <tr>
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
                    <th>Total</th>
                </tr>
                <?php
                $query = "SELECT * FROM $table_name ORDER BY date DESC, total ASC";
                $result = $conn->query($query);
                $display_id = 1;
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $display_id++ . "</td>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
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
                    echo "<tr><td colspan='11'>No results found</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html> 