<?php
session_start();
include "db.php";

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

// Get the selected table name from URL parameter
$selected_table = isset($_GET['table']) ? filter_var($_GET['table'], FILTER_SANITIZE_STRING) : 'pigeon_race_results';

// Validate table name
if (!preg_match('/^[a-zA-Z0-9_]+$/', $selected_table)) {
    $selected_table = 'pigeon_race_results';
}

// Check if table exists
$check_table = $conn->query("SHOW TABLES LIKE '$selected_table'");
if ($check_table && $check_table->num_rows === 0) {
    $selected_table = 'pigeon_race_results';
}

// Get current home table from settings
$home_table = 'pigeon_race_results'; // Default value
$home_table_result = $conn->query("SELECT value FROM settings WHERE name = 'home_table'");
if ($home_table_result && $home_table_result->num_rows > 0) {
    $home_table = $home_table_result->fetch_assoc()['value'];
}

// Get all race result tables for navbar
$tables_query = "SHOW TABLES LIKE '%_race_results'";
$tables_result = $conn->query($tables_query);
$tables = [];
if ($tables_result) {
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }
}

// Get data for selected table
$query = "SELECT * FROM $selected_table ORDER BY date DESC, total ASC";
$result = $conn->query($query);
$display_id = 1;

// Check if query was successful
if (!$result) {
    // If query failed, try to get data from default table
    $selected_table = 'pigeon_race_results';
    $query = "SELECT * FROM $selected_table ORDER BY date DESC, total ASC";
    $result = $conn->query($query);
}

// Initialize result array
$rows = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Website</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>
    @keyframes blinkCell {
        0% { background-color: #4CAF50; color: white; }
        50% { background-color: transparent; color: inherit; }
        100% { background-color: #4CAF50; color: white; }
    }

    .blink {
        animation: blinkCell 1s ease-in-out infinite;
    }

    .table-container table td {
        transition: all 0.3s ease;
    }

    .summary-cards {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
        flex-wrap: wrap;
        gap: 20px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        min-width: 250px;
        text-align: center;
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card h2 {
        color: #333;
        margin-bottom: 10px;
        font-size: 1.5em;
    }

    .card p {
        font-size: 2em;
        color: #4CAF50;
        margin: 15px 0;
        font-weight: bold;
    }

    .card small {
        color: #666;
        display: block;
        font-size: 0.9em;
    }

    .total { border-top: 4px solid #4CAF50; }
    .flown { border-top: 4px solid #2196F3; }
    .remaining { border-top: 4px solid #f44336; }
  </style>
</head>
<body>
<div class="swiper mySwiper">
<div class="swiper-wrapper">
        <div class="swiper-slide"><img src="./images/1.jpeg" alt="Image 1"></div>
        <div class="swiper-slide"><img src="./images/2.jpeg" alt="Image 2"></div>
        <div class="swiper-slide"><img src="./images/3.jpeg" alt="Image 3"></div>
    </div>
</div> 

  <div class="navbar">
    <a href="#home">Home</a>
    <a href="#contact">Contact</a>
    <a href="login.php">LOGIN</a>
    <?php
    // Get all tables from the database that start with 'pigeon'
    $tables_query = "SHOW TABLES LIKE 'pigeon%'";
    $tables_result = $conn->query($tables_query);
    
    if ($tables_result) {
        while ($row = $tables_result->fetch_array()) {
            $table_name = $row[0];
            // Skip system tables if any
            if ($table_name !== 'password_history') {
                $display_name = str_replace(['_race_results', 'pigeon_'], '', $table_name);
                $display_name = ucfirst($display_name);
                $is_active = isset($_GET['table']) ? ($_GET['table'] === $table_name) : ($table_name === $home_table);
                echo "<a href='?table=" . urlencode($table_name) . "' " . ($is_active ? "class='active'" : "") . ">" . $display_name . "</a>";
            }
        }
    }
    ?>
  </div>

  <h1 id='welcome-note'>Welcome to Our Website!</h1>

  <div class="news-ticker-container">
    <p class="news-label">Latest news:</p>
    <p class="news-content">خوش آمدید سب پلئیرز کو بیسٹ آف لک اپنا شوک کمیٹی کی طرف سے۔۔۔ اپنا شوک ایک ہی ممبر آف لا دیا ہے جلد ہی متوقع</p>
    <div class="facebook-icon">
      <img src="./images/facebook.png" alt="Facebook Like">
    </div>
  </div>

 <div class="container">
        <h2 class="pigeon-info">Pigeon Race Summary</h2>

        <div class="summary-cards">
            <div class="card total">
                <h2>Total Pigeons</h2>
                <p id="totalPigeons">Loading...</p>
                <small>Total number of pigeons in the race</small>
            </div>
            <div class="card flown">
                <h2>Pigeons Flown</h2>
                <p id="totalFlown">Loading...</p>
                <small>Number of pigeons that have completed the race</small>
            </div>
            <div class="card remaining">
                <h2>Remaining Pigeons</h2>
                <p id="remainingPigeons">Loading...</p>
                <small>Number of pigeons yet to complete</small>
            </div>
        </div>

        <p>This is a placeholder for the main content of the website.</p>

        <h2 class="pigeon-info">Pigeon Race Results</h2>

<div class="table-container">
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
          </tr>
  
          <?php if (!empty($rows)): ?>
              <?php foreach ($rows as $row): ?>
              <tr>
                  <td><?= $display_id++ ?></td>
                  <td><?= $row['date'] ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= $row['pigeon1'] ?></td>
                  <td><?= $row['pigeon2'] ?></td>
                  <td><?= $row['pigeon3'] ?></td>
                  <td><?= $row['pigeon4'] ?></td>
                  <td><?= $row['pigeon5'] ?></td>
                  <td><?= $row['pigeon6'] ?></td>
                  <td><?= $row['pigeon7'] ?></td>
                  <td><?= $row['total'] ?></td>
              </tr>
              <?php endforeach; ?>
          <?php else: ?>
              <tr>
                  <td colspan="11" style="text-align: center;">No results found</td>
              </tr>
          <?php endif; ?>
  
      </table>
      </div>
  
<footer>

</footer>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
function updateSummary() {
    // Get the current table from URL or use default
    const urlParams = new URLSearchParams(window.location.search);
    const currentTable = urlParams.get('table') || 'pigeon_race_results';
    
    fetch(`summary_data.php?table=${encodeURIComponent(currentTable)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            document.getElementById("totalPigeons").innerText = data.totalPigeons;
            document.getElementById("totalFlown").innerText = data.flownPigeons;
            document.getElementById("remainingPigeons").innerText = data.remainingPigeons;
        })
        .catch(error => {
            console.error("Error fetching data:", error);
            document.getElementById("totalPigeons").innerText = "Error";
            document.getElementById("totalFlown").innerText = "Error";
            document.getElementById("remainingPigeons").innerText = "Error";
        });
}

// Function to blink a cell
function blinkCell(cell) {
    // Add blink class
    cell.classList.add('blink');
    
    // Remove blink class after 5 seconds
    setTimeout(() => {
        cell.classList.remove('blink');
    }, 5000);
}

// Function to refresh table data
function refreshTableData() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('.table-container table');
            const currentTable = document.querySelector('.table-container table');
            
            if (newTable && currentTable) {
                // Store current values before update
                const currentCells = currentTable.getElementsByTagName('td');
                const currentValues = Array.from(currentCells).map(cell => cell.textContent);
                
                // Update table content
                currentTable.innerHTML = newTable.innerHTML;
                
                // Compare and blink changed cells
                const newCells = currentTable.getElementsByTagName('td');
                Array.from(newCells).forEach((cell, index) => {
                    if (currentValues[index] && cell.textContent !== currentValues[index]) {
                        blinkCell(cell);
                    }
                });
            }
        })
        .catch(error => console.error("Error refreshing table:", error));
}

// Initialize when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initial setup
    updateSummary();
    
    // Set up auto-refresh
    setInterval(() => {
        updateSummary();
        refreshTableData();
    }, 5000);
});

function openEditModal(id, name, date, pigeon1, pigeon2, pigeon3, pigeon4, pigeon5, pigeon6, pigeon7) {
    // Implementation of the function
}
</script>
<script src="script.js"></script>
</body>
</html>
