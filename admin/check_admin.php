<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

echo "<h2>Admin Status Check</h2>";

// Check if user is logged in
echo "<h3>Session Information:</h3>";
echo "User ID in session: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Username in session: " . ($_SESSION['username'] ?? 'Not set') . "<br>";

// Check database connection
echo "<h3>Database Connection:</h3>";
if ($conn) {
    echo "Database connection successful<br>";
} else {
    echo "Database connection failed<br>";
}

// Check admin user in database
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo "<h3>User Information:</h3>";
    echo "User ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "<br>";
    
    // Check is_admin() function
    echo "<h3>is_admin() Function Test:</h3>";
    echo "is_admin() returns: " . (is_admin() ? 'true' : 'false') . "<br>";
}

// List all admin users
echo "<h3>All Admin Users:</h3>";
$admin_query = "SELECT id, username, email, is_admin FROM users WHERE is_admin = 1";
$admin_result = mysqli_query($conn, $admin_query);
while ($admin = mysqli_fetch_assoc($admin_result)) {
    echo "ID: " . $admin['id'] . ", Username: " . $admin['username'] . ", Email: " . $admin['email'] . "<br>";
}
?> 