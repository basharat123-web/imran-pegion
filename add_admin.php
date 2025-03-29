<?php
include "db.php";

// Check if admin user already exists
$check_admin = "SELECT * FROM users WHERE username = 'admin' AND role = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Create admin user
    $username = 'admin';
    $password = 'admin123';
    $name = 'Administrator';
    $role = 'admin';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $insert_admin = "INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_admin);
    $stmt->bind_param("ssss", $username, $hashed_password, $name, $role);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
} else {
    echo "Admin user already exists";
}

$conn->close();
?> 