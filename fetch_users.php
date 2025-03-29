<?php
session_start();
include "db.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<tr><td colspan='6'>Unauthorized access.</td></tr>";
    exit();
}

// Query to fetch all users and their most recent password reset
$query = "
    SELECT u.id, u.username, u.name, u.role, prh.new_password
    FROM users u
    LEFT JOIN (
        SELECT user_id, new_password
        FROM password_reset_history
        WHERE (user_id, reset_at) IN (
            SELECT user_id, MAX(reset_at)
            FROM password_reset_history
            GROUP BY user_id
        )
    ) prh ON u.id = prh.user_id
    ORDER BY u.id
";
$result = $conn->query($query);

// Start building the HTML table
echo '<tr>
        <th>ID</th>
        <th>Username</th>
        <th>Name</th>
        <th>Role</th>
        <th>Latest Password</th>
        <th>Actions</th>
      </tr>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . ($row['new_password'] ? htmlspecialchars($row['new_password']) : 'Not reset yet') . "</td>";
        echo "<td>
                <button class='action-btn reset-btn' onclick=\"resetPassword('" . htmlspecialchars($row['id']) . "')\">Reset Password</button>
                <button class='action-btn delete-user-btn' onclick=\"deleteUser('" . htmlspecialchars($row['id']) . "')\">Delete User</button>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No users found.</td></tr>";
}

$conn->close();
?>