<?php
session_start();
include "db.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Create settings table if it doesn't exist
$create_settings_table = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($create_settings_table);

// Insert default home table setting if it doesn't exist
$insert_default_setting = "INSERT INTO settings (name, value) 
    SELECT 'home_table', 'pigeon_race_results' 
    WHERE NOT EXISTS (SELECT 1 FROM settings WHERE name = 'home_table')";
$conn->query($insert_default_setting);

// Handle setting home table
if (isset($_POST['set_home_table'])) {
    $table_name = filter_var($_POST['table_name'], FILTER_SANITIZE_STRING);
    if (preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        // Update the settings table
        $conn->query("UPDATE settings SET value = '$table_name' WHERE name = 'home_table'");
        if ($conn->affected_rows === 0) {
            $conn->query("INSERT INTO settings (name, value) VALUES ('home_table', '$table_name')");
        }
        $success_message = "Home table updated successfully!";
    }
}

// Get current home table
$home_table = 'pigeon_race_results'; // Default value
$home_table_result = $conn->query("SELECT value FROM settings WHERE name = 'home_table'");
if ($home_table_result && $home_table_result->num_rows > 0) {
    $home_table = $home_table_result->fetch_assoc()['value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #b993d6 0%, #8ca6db 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 95%;
            margin: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .dashboard-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .dashboard-section {
            background: white;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .section-header h3 {
            color: #2c3e50;
            margin: 0;
            font-size: 20px;
        }
        .section-header .icon {
            font-size: 24px;
            color: #3498db;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .success-message {
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            display: none;
            animation: fadeOut 3s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        .btn-success:hover {
            background: #27ae60;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .table-responsive {
            overflow-x: auto;
            margin-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .action-btn {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
        <h2>Admin Dashboard</h2>
            <a href="?logout=true" class="logout-btn">Logout</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Table Management Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3>🗃️ Table Management</h3>
            </div>
            <div class="grid-container">
                <!-- Create New Table Card -->
                <div class="card">
                    <h4>Create New Table</h4>
                    <form id="createTableForm">
                        <div class="form-group">
                            <input type="text" class="form-control" name="table_name" id="table_name" 
                                   placeholder="Enter Table Name (e.g., pigeon_race_2024)" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Table</button>
                    </form>
                </div>

                <!-- Select Active Table Card -->
                <div class="card">
                    <h4>Select Active Table</h4>
                    <div class="form-group">
                        <select id="tableSelector" class="form-control">
                            <option value="pigeon_race_results">Default Table</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Existing Tables List -->
            <div class="table-responsive">
                <table>
                    <tr>
                        <th>Table Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    // Get all tables from the database that start with 'pigeon'
                    $tables_query = "SHOW TABLES LIKE 'pigeon%'";
                    $tables_result = $conn->query($tables_query);
                    
                    if ($tables_result) {
                        while ($row = $tables_result->fetch_array()) {
                            $table_name = $row[0];
                            if ($table_name !== 'password_history') {
                                $display_name = str_replace(['_race_results', 'pigeon_'], '', $table_name);
                                $display_name = ucfirst($display_name);
                                $is_home = $table_name === $home_table;
                                
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($display_name) . "</td>";
                                echo "<td>";
                                if ($is_home) {
                                    echo "<span class='badge badge-success'>Home Table</span>";
                                } else {
                                    echo "<span class='badge badge-secondary'>Active</span>";
                                }
                                echo "</td>";
                                echo "<td>";
                                if (!$is_home) {
                                    echo "<button class='action-btn btn-danger' onclick='deleteTable(\"$table_name\")'>Delete</button>";
                                    echo "<form method='POST' style='display: inline;'>";
                                    echo "<input type='hidden' name='table_name' value='$table_name'>";
                                    echo "<button type='submit' name='set_home_table' class='action-btn btn-success'>Set as Home</button>";
                                    echo "</form>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </table>
            </div>
        </div>

        <!-- Race Results Management Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3>🏁 Race Results Management</h3>
            </div>
        <form id="raceForm">
                <div class="grid-container">
                    <div class="form-group">
                        <input type="text" class="form-control" name="name" id="name" placeholder="Enter Participant Name" required>
                    </div>
                    <div class="form-group">
                        <input type="date" class="form-control" name="date" id="date" required>
                    </div>
                </div>
                <div class="grid-container">
                    <?php for($i = 1; $i <= 7; $i++): ?>
                    <div class="form-group">
                        <input type="text" class="form-control" name="pigeon<?php echo $i; ?>" 
                               id="pigeon<?php echo $i; ?>" placeholder="Pigeon <?php echo $i; ?> Time (HH:MM:SS)"
                               pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                        <div class="error" id="pigeon<?php echo $i; ?>-error">Please enter a valid time format (HH:MM:SS)</div>
                    </div>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn btn-primary">Add Race Result</button>
        </form>

            <div class="table-responsive">
        <table id="raceTable">
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
                <th>Actions</th>
            </tr>
        </table>
            </div>
    </div>

        <!-- User Management Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3>👥 User Management</h3>
            </div>
            <div class="grid-container">
                <div class="card">
                    <h4>Add New User</h4>
            <form id="addUserForm">
                        <div class="form-group">
                            <input type="text" class="form-control" name="username" id="addUsername" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" id="addPassword" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="name" id="addName" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <select name="role" id="addRole" class="form-control" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="client">Client</option>
                    <option value="admin">Admin</option>
                </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add User</button>
            </form>
                </div>
            </div>

            <div class="table-responsive">
            <table class="user-table" id="userTable">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Latest Password</th>
                    <th>Actions</th>
                </tr>
            </table>
        </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">×</span>
            <h2>Edit Race Result</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <input type="text" class="form-control" name="name" id="editName" placeholder="Enter Participant Name" required>
                </div>
                <div class="form-group">
                    <input type="date" class="form-control" name="date" id="editDate" required>
                </div>
                <?php for($i = 1; $i <= 7; $i++): ?>
                <div class="form-group">
                    <input type="text" class="form-control" name="pigeon<?php echo $i; ?>" 
                           id="editPigeon<?php echo $i; ?>" placeholder="Pigeon <?php echo $i; ?> Time (HH:MM:SS)"
                           pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                    <div class="error" id="editPigeon<?php echo $i; ?>-error">Please enter a valid time format (HH:MM:SS)</div>
                </div>
                <?php endfor; ?>
                <button type="submit" class="btn btn-primary">Update Race Result</button>
            </form>
        </div>
    </div>

    <script>
        // Load Available Tables
        function loadTables() {
            fetch("fetch_tables.php")
                .then(response => response.json())
                .then(tables => {
                    const selector = document.getElementById("tableSelector");
                    selector.innerHTML = ''; // Clear existing options
                    tables.forEach(table => {
                        const option = document.createElement('option');
                        option.value = table;
                        option.textContent = table;
                        selector.appendChild(option);
                    });
                    // Load data for the selected table
                    loadRaceResults();
                })
                .catch(error => console.error("Error fetching tables:", error));
        }

        // Update table selection handler
        document.getElementById("tableSelector").addEventListener("change", function() {
            loadRaceResults();
        });

        // Modified Load Race Results
        function loadRaceResults() {
            const selectedTable = document.getElementById("tableSelector").value;
            fetch("fetch.php?table=" + encodeURIComponent(selectedTable))
                .then(response => response.text())
                .then(data => {
                    const raceTable = document.getElementById("raceTable");
                    // Get the table body content without the header
                    const tableBody = data.substring(data.indexOf('</tr>') + 5);
                    // Keep only our original header and add the new data
                    raceTable.innerHTML = raceTable.rows[0].outerHTML + tableBody;
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        // Modified Insert Form Submission
        document.getElementById("raceForm").addEventListener("submit", function(event) {
            event.preventDefault();

            // Reset error messages
            document.querySelectorAll('.error').forEach(error => error.style.display = 'none');

            // Validate time inputs
            let isValid = true;
            const timeInputs = ['pigeon1', 'pigeon2', 'pigeon3', 'pigeon4', 'pigeon5', 'pigeon6', 'pigeon7'];

            timeInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                const value = input.value.trim();
                const errorElement = document.getElementById(`${inputId}-error`);

                // Allow empty inputs (will be treated as 00:00:00 by the backend)
                if (value === '') {
                    input.value = '00:00:00';
                    return;
                }

                // Validate HH:MM:SS format
                const timePattern = /^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/;
                if (!timePattern.test(value)) {
                    errorElement.style.display = 'block';
                    isValid = false;
                }
            });

            if (!isValid) {
                alert("Please correct the time inputs before submitting.");
                return;
            }

            // Add the selected table to the form data
            let formData = new FormData(this);
            const selectedTable = document.getElementById("tableSelector").value;
            formData.append('table', selectedTable);

            // Submit the form
            fetch("insert.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Success")) {
                    alert("Data added successfully!");
                    loadRaceResults(); // Reload the current table's data
                    document.getElementById("raceForm").reset();
                    // Set today's date as default
                    document.getElementById("date").valueAsDate = new Date();
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        });

        // Load Users
        function loadUsers() {
            fetch("fetch_users.php")
                .then(response => response.text())
                .then(data => {
                    document.getElementById("userTable").innerHTML = data;
                })
                .catch(error => console.error("Error fetching users:", error));
        }

        // Modified Edit Form Submission
        document.getElementById("editForm").addEventListener("submit", function(event) {
            event.preventDefault();

            // Reset error messages
            document.querySelectorAll('.error').forEach(error => error.style.display = 'none');

            // Validate time inputs
            let isValid = true;
            const timeInputs = ['editPigeon1', 'editPigeon2', 'editPigeon3', 'editPigeon4', 'editPigeon5', 'editPigeon6', 'editPigeon7'];

            timeInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                const value = input.value.trim();
                const errorElement = document.getElementById(`${inputId}-error`);

                // Allow empty inputs (will be treated as 00:00:00 by the backend)
                if (value === '') {
                    input.value = '00:00:00';
                    return;
                }

                // Validate HH:MM:SS format
                const timePattern = /^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/;
                if (!timePattern.test(value)) {
                    errorElement.style.display = 'block';
                    isValid = false;
                }
            });

            if (!isValid) {
                alert("Please correct the time inputs before submitting.");
                return;
            }

            // Add the selected table to the form data
            let formData = new FormData(this);
            formData.append('table', document.getElementById("tableSelector").value);

            // Submit the edit form
            fetch("update.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Success")) {
                    alert("Data updated successfully!");
                    closeModal();
                    loadRaceResults();
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        });

        // Modified Open Edit Modal
        function openEditModal(id, name, date, pigeon1, pigeon2, pigeon3, pigeon4, pigeon5, pigeon6, pigeon7) {
            document.getElementById("editId").value = id;
            document.getElementById("editName").value = name;
            document.getElementById("editDate").value = date;
            document.getElementById("editPigeon1").value = pigeon1;
            document.getElementById("editPigeon2").value = pigeon2;
            document.getElementById("editPigeon3").value = pigeon3;
            document.getElementById("editPigeon4").value = pigeon4;
            document.getElementById("editPigeon5").value = pigeon5;
            document.getElementById("editPigeon6").value = pigeon6;
            document.getElementById("editPigeon7").value = pigeon7;
            document.getElementById("editModal").style.display = "flex";
        }

        // Close Modal
        function closeModal() {
            document.getElementById("editModal").style.display = "none";
            document.getElementById("editForm").reset();
            document.querySelectorAll('.error').forEach(error => error.style.display = 'none');
        }

        // Modified Delete Row
        function deleteRow(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                const selectedTable = document.getElementById("tableSelector").value;
                fetch("delete.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id=" + id + "&table=" + encodeURIComponent(selectedTable)
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("Success")) {
                        alert("Data deleted successfully!");
                        loadRaceResults();
                    } else {
                        alert("Error: " + data);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        }

        // Reset Password
        function resetPassword(userId) {
            const newPassword = prompt("Enter the new password for the user:");
            if (newPassword) {
                fetch("reset_password.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "user_id=" + userId + "&new_password=" + encodeURIComponent(newPassword)
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("Success")) {
                        alert("Password reset successfully!");
                        loadUsers(); // Refresh the user table to show the new password
                    } else {
                        alert("Error: " + data);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        }

        // Delete User
        function deleteUser(userId) {
            if (confirm("Are you sure you want to delete this user? This will also delete all their race results and password history.")) {
                fetch("delete_user.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "user_id=" + userId
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("Success")) {
                        alert("User deleted successfully!");
                        loadUsers(); // Refresh the user table
                        loadRaceResults(); // Refresh the race results table
                    } else {
                        alert("Error: " + data);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        }

        // Create Table Form Submission
        document.getElementById("createTableForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            fetch("create_table.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Success")) {
                    alert(data);
                    document.getElementById("createTableForm").reset();
                    loadTables(); // Reload the table list
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        });

        // Add function to delete table
        function deleteTable(tableName) {
            if (confirm("Are you sure you want to delete this table? This action cannot be undone!")) {
                fetch("delete_table.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "table_name=" + encodeURIComponent(tableName)
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("Success")) {
                        alert("Table deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        }

        // Show success message and hide it after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'block';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            }
        });

        window.onload = function() {
            loadTables(); // This will also load race results for the selected table
            loadUsers();
            // Set today's date as default
            document.getElementById("date").valueAsDate = new Date();
        };
    </script>
</body>
</html>