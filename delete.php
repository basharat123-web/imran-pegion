<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        echo "Error: Please log in to delete data.";
        exit();
    }

    // Sanitize the ID
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    // For clients, verify that the record belongs to them
    if ($_SESSION['role'] !== 'admin') {
        $check_stmt = $conn->prepare("SELECT user_id FROM pigeon_race_results WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows === 0 || $result->fetch_assoc()['user_id'] !== $_SESSION['user_id']) {
            echo "Error: Unauthorized action.";
            $check_stmt->close();
            $conn->close();
            exit();
        }
        $check_stmt->close();
    }

    // Prepare and execute the DELETE statement
    $stmt = $conn->prepare("DELETE FROM pigeon_race_results WHERE id = ?");
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>