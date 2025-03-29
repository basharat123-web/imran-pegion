<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's name
$logged_in_user_name = $_SESSION['name'];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pigeon Race Data Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: linear-gradient(to right, #b993d6, #8ca6db);
        }
        .container {
            width: 50%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
        }
        input, button {
            padding: 10px;
            margin: 5px;
            width: 80%;
            display: block;
            margin: auto;
            box-sizing: border-box;
        }
        input[type="text"] {
            font-family: monospace; /* Makes time input easier to read */
        }
        input:invalid {
            border: 2px solid red;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
        }
        .table-container {
  width: 100%;
  overflow-x: auto; /* Enables horizontal scrolling */
  display: block;
  white-space: nowrap;
}
        th, td {
            padding: 10px;
            border: 1px solid gray;
            text-align: center;
        }
        th {
            background: green;
            color: white;
        }
        .error {
            color: red;
            font-size: 0.9em;
            display: none;
        }
        /* Action Buttons (Edit and Delete) */
        .action-btn {
            padding: 8px 12px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }
        .edit-btn {
            background-color: #26a69a; /* Teal background for Edit */
        }
        .edit-btn:hover {
            background-color: #1d7d74; /* Darker teal on hover */
        }
        .delete-btn {
            background-color: #ef5350; /* Red background for Delete */
        }
        .delete-btn:hover {
            background-color: #c62828; /* Darker red on hover */
        }
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }
        .modal-content h2 {
            margin-top: 0;
            color: #4CAF50;
        }
        .modal-content input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        .modal-content button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .modal-content button:hover {
            background-color: #45a049;
        }
        /* Logout Button */
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #ef5350;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background-color: #c62828;
        }
        @media screen and (max-width: 600px) {
.container {
    width: 90%
}
}
  
    </style>
</head>
<body>

    <div class="container">
        <h2>Pigeon Race - Data Entry</h2>
        <p>Welcome, <?php echo htmlspecialchars($logged_in_user_name); ?>! <a href="?logout=true" class="logout-btn">Logout</a></p>

        <form id="raceForm">
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($logged_in_user_name); ?>" readonly>
            <input type="text" name="pigeon1" id="pigeon1" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon1-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <input type="text" name="pigeon2" id="pigeon2" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon2-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <input type="text" name="pigeon3" id="pigeon3" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-5][0-9]:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon3-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <input type="text" name="pigeon4" id="pigeon4" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon4-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <input type="text" name="pigeon5" id="pigeon5" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon5-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <input type="text" name="pigeon6" id="pigeon6" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon6-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <input type="text" name="pigeon7" id="pigeon7" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
            <div class="error" id="pigeon7-error">Please enter a valid time in HH:MM:SS format (e.g., 12:59:00).</div>
            <button type="submit">Add Race Result</button>
        </form>

        <h3>Race Results</h3>
        <div class="table-container">
        <table id="raceTable">
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
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">×</span>
            <h2>Edit Race Result</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="editId">
                <input type="text" name="name" id="editName" value="<?php echo htmlspecialchars($logged_in_user_name); ?>" readonly>
                <input type="text" name="pigeon1" id="editPigeon1" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon1-error">Please enter a valid time in HH:MM:SS format.</div>
                <input type="text" name="pigeon2" id="editPigeon2" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon2-error">Please enter a valid time in HH:MM:SS format.</div>
                <input type="text" name="pigeon3" id="editPigeon3" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-5][0-9]:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon3-error">Please enter a valid time in HH:MM:SS format.</div>
                <input type="text" name="pigeon4" id="editPigeon4" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon4-error">Please enter a valid time in HH:MM:SS format.</div>
                <input type="text" name="pigeon5" id="editPigeon5" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon5-error">Please enter a valid time in HH:MM:SS format.</div>
                <input type="text" name="pigeon6" id="editPigeon6" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon6-error">Please enter a valid time in HH:MM:SS format.</div>
                <input type="text" name="pigeon7" id="editPigeon7" placeholder="HH:MM:SS (e.g., 12:59:00)" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]">
                <div class="error" id="editPigeon7-error">Please enter a valid time in HH:MM:SS format.</div>
                <button type="submit">Update Race Result</button>
            </form>
        </div>
    </div>

    <script>
        // Insert Form Submission
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

            // Submit the form
            let formData = new FormData(this);
            fetch("insert.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Success")) {
                    alert("Data added successfully!");
                    loadRaceResults();
                    document.getElementById("raceForm").reset();
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => console.error("Error:", error));
        });

        // Edit Form Submission
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

            // Submit the edit form
            let formData = new FormData(this);
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

        // Load Race Results
        function loadRaceResults() {
            fetch("fetch.php")
                .then(response => response.text())
                .then(data => {
                    document.getElementById("raceTable").innerHTML = data;
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        // Open Edit Modal
        function openEditModal(id, name, pigeon1, pigeon2, pigeon3, pigeon4, pigeon5, pigeon6, pigeon7) {
            document.getElementById("editId").value = id;
            document.getElementById("editName").value = name;
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

        // Delete Row
        function deleteRow(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                fetch("delete.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id=" + id
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

        window.onload = loadRaceResults;
    </script>
</body>
</html>