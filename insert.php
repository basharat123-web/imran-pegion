<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        echo "Error: Please log in to add data.";
        exit();
    }

    // Get and validate table name
    $table_name = filter_var($_POST['table'], FILTER_SANITIZE_STRING);
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        echo "Error: Invalid table name";
        exit();
    }

    // Check if table exists
    $check_table = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($check_table->num_rows === 0) {
        echo "Error: Table does not exist";
        exit();
    }

    // Sanitize and retrieve form data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $p1 = filter_input(INPUT_POST, 'pigeon1', FILTER_SANITIZE_STRING) ?: '00:00:00';
    $p2 = filter_input(INPUT_POST, 'pigeon2', FILTER_SANITIZE_STRING) ?: '00:00:00';
    $p3 = filter_input(INPUT_POST, 'pigeon3', FILTER_SANITIZE_STRING) ?: '00:00:00';
    $p4 = filter_input(INPUT_POST, 'pigeon4', FILTER_SANITIZE_STRING) ?: '00:00:00';
    $p5 = filter_input(INPUT_POST, 'pigeon5', FILTER_SANITIZE_STRING) ?: '00:00:00';
    $p6 = filter_input(INPUT_POST, 'pigeon6', FILTER_SANITIZE_STRING) ?: '00:00:00';
    $p7 = filter_input(INPUT_POST, 'pigeon7', FILTER_SANITIZE_STRING) ?: '00:00:00';

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert the new row
        $stmt = $conn->prepare("INSERT INTO $table_name (date, name, pigeon1, pigeon2, pigeon3, pigeon4, pigeon5, pigeon6, pigeon7) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing INSERT statement: " . $conn->error);
        }

        $stmt->bind_param("sssssssss", $date, $name, $p1, $p2, $p3, $p4, $p5, $p6, $p7);

        if (!$stmt->execute()) {
            throw new Exception("Error executing INSERT statement: " . $stmt->error);
        }

        // Get the ID of the newly inserted row
        $new_id = $conn->insert_id;

        // Calculate the total for the new row (if total is not computed automatically)
        $update_query = "
            UPDATE $table_name
            SET total = SEC_TO_TIME(
                TIME_TO_SEC(IFNULL(pigeon1, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon2, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon3, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon4, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon5, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon6, '00:00:00')) +
                TIME_TO_SEC(IFNULL(pigeon7, '00:00:00'))
            )
            WHERE id = ?
        ";

        $update_stmt = $conn->prepare($update_query);
        if (!$update_stmt) {
            throw new Exception("Error preparing total UPDATE statement: " . $conn->error);
        }

        $update_stmt->bind_param("i", $new_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Error executing total UPDATE statement: " . $update_stmt->error);
        }

        // Commit the transaction
        $conn->commit();
        echo "Success";

        $stmt->close();
        $update_stmt->close();
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>