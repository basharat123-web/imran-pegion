<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_POST['username'];
$password = $_POST['password']; // Get password as plain text

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Debugging: Check if user exists
if (!$user) {
    die("Error: User not found in database.");
}
// Debugging: Print the result
// Ensure $user is an array before accessing its values
if (is_array($user) && isset($user['password'])) {
    if ($password === $user['password']) {  // Direct comparison if storing plain text
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Password mismatch.";
    }
} else {
    echo "Error: User data is not valid.";
}
?>
