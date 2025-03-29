<?php
session_start();
include "db.php";

// Function to create and set up admin table
function setupAdminTable($conn) {
    // Create admin table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL
    )";
    
    if ($conn->query($create_table)) {
        // Check if admin user exists
        $check_admin = "SELECT * FROM admin WHERE username = 'admin'";
        $result = $conn->query($check_admin);
        
        if ($result->num_rows == 0) {
            // Create default admin user
            $username = 'admin';
            $password = 'admin123';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_admin = "INSERT INTO admin (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_admin);
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                return true;
            }
        }
    }
    return false;
}

// Set up admin table
setupAdminTable($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $login_type = $_POST['login_type']; // 'admin' or 'client'

    // Use the same users table for both admin and client
    $stmt = $conn->prepare("SELECT id, username, password, name, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($login_type === 'admin' && $user['role'] === 'admin') {
                // Admin login successful
                $_SESSION['admin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header("Location: admin_dashboard.php");
                exit();
            } else if ($login_type === 'client' && $user['role'] === 'client') {
                // Client login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid login type for this user";
            }
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Username not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            width: '100%';
            height: '100%';
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .main-container {
            width: 100%;
            height: 100vh;
            text-align: center;
            background: linear-gradient(to right, #b993d6, #8ca6db);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            width: 30%;
            height: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
        }
        #form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 15px;
        }
        input, button, select {
            padding: 10px;
            margin: 10px;
            width: 100%;
            display: block;
            margin: auto;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: #f44336;
            margin-bottom: 10px;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
        }
        select {
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
        }
        .login-info {
            margin-top: 15px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 4px;
            font-size: 0.9em;
        }
        @media screen and (max-width: 600px) {
            .login-container {
                width: 90%;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-container">
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form id="form" method="POST" action="login.php">
                <select name="login_type" required>
                    <option value="">Select Login Type</option>
                    <option value="client">Client Login</option>
                    <option value="admin">Admin Login</option>
                </select>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="login-info">
                <p>Admin Login: Use your admin username and password</p>
            </div>
        </div>
    </div>
</body>
</html>