<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get table name from URL
$table_name = isset($_GET['table']) ? $_GET['table'] : '';

if (!$table_name) {
    header("Location: admin_dashboard.php");
    exit();
}

// Handle row deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM $table_name WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Handle row addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_row'])) {
    $columns = array_keys($_POST);
    $values = array_values($_POST);
    
    // Remove 'add_row' from columns and values
    array_pop($columns);
    array_pop($values);
    
    $insert_query = "INSERT INTO $table_name (" . implode(", ", $columns) . ") VALUES (" . str_repeat("?,", count($values)-1) . "?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param(str_repeat("s", count($values)), ...$values);
    $stmt->execute();
}

// Get table structure
$columns_query = "SHOW COLUMNS FROM $table_name";
$columns_result = $conn->query($columns_query);
$columns = [];
while ($column = $columns_result->fetch_assoc()) {
    if ($column['Field'] !== 'id') {
        $columns[] = $column['Field'];
    }
}

// Get table data
$data_query = "SELECT * FROM $table_name ORDER BY id DESC";
$data_result = $conn->query($data_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage <?php echo $table_name; ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .manage-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-danger {
            background: #f44336;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="manage-container">
        <h1>Manage <?php echo $table_name; ?></h1>
        
        <div class="section">
            <h2>Add New Row</h2>
            <form method="POST">
                <?php foreach ($columns as $column) { ?>
                    <div class="form-group">
                        <label for="<?php echo $column; ?>"><?php echo ucfirst($column); ?>:</label>
                        <input type="text" id="<?php echo $column; ?>" name="<?php echo $column; ?>" required>
                    </div>
                <?php } ?>
                <button type="submit" name="add_row" class="btn">Add Row</button>
            </form>
        </div>
        
        <div class="section">
            <h2>Table Data</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <?php foreach ($columns as $column) { ?>
                        <th><?php echo ucfirst($column); ?></th>
                    <?php } ?>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $data_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <?php foreach ($columns as $column) { ?>
                            <td><?php echo $row[$column]; ?></td>
                        <?php } ?>
                        <td class="actions">
                            <a href="?table=<?php echo $table_name; ?>&delete=<?php echo $row['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this row?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        
        <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html> 