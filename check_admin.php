<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if admin exists
$query = "SELECT * FROM users WHERE email = 'admin@godzillawear.com'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    echo "Admin user exists:<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "<br>";
    echo "Password Hash: " . $admin['password'] . "<br>";
    
    // Test password verification
    $test_password = 'admin123';
    if (verify_password($test_password, $admin['password'])) {
        echo "<br>Password verification successful!";
    } else {
        echo "<br>Password verification failed!";
    }
} else {
    echo "Admin user does not exist!";
}
?> 