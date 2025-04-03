<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin credentials - in production, these should be set through a secure form
$admin_username = 'admin';
$admin_email = 'admin@godzillawear.com';
$admin_password = 'admin123'; // This should be changed after first login
$admin_first_name = 'Admin';
$admin_last_name = 'User';

// Hash the password
$hashed_password = hash_password($admin_password);

// Check if admin already exists using prepared statement
$check_stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ? OR email = ?");
if (!$check_stmt) {
    die("Error preparing statement: " . $conn->error);
}

$check_stmt->bind_param("ss", $admin_username, $admin_email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    $update_stmt = $conn->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE username = ? OR email = ?");
    if (!$update_stmt) {
        die("Error preparing update statement: " . $conn->error);
    }
    
    $update_stmt->bind_param("sss", $hashed_password, $admin_username, $admin_email);
    
    if ($update_stmt->execute()) {
        echo "Admin user updated successfully!<br>";
        echo "Username: $admin_username<br>";
        echo "Email: $admin_email<br>";
        echo "Password: $admin_password<br>";
        echo "<strong>Please change this password after first login!</strong>";
    } else {
        echo "Error updating admin user: " . $update_stmt->error;
    }
    $update_stmt->close();
} else {
    // Create new admin user
    $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES (?, ?, ?, ?, ?, 1)");
    if (!$insert_stmt) {
        die("Error preparing insert statement: " . $conn->error);
    }
    
    $insert_stmt->bind_param("sssss", $admin_username, $admin_email, $hashed_password, $admin_first_name, $admin_last_name);
    
    if ($insert_stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Username: $admin_username<br>";
        echo "Email: $admin_email<br>";
        echo "Password: $admin_password<br>";
        echo "<strong>Please change this password after first login!</strong>";
    } else {
        echo "Error creating admin user: " . $insert_stmt->error;
    }
    $insert_stmt->close();
}

$check_stmt->close();

// Verify admin creation
$verify_stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ?");
$verify_stmt->bind_param("s", $admin_username);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$admin_user = $verify_result->fetch_assoc();

if ($admin_user && $admin_user['is_admin'] == 1) {
    echo "<br>Admin verification successful! User ID: " . $admin_user['id'];
} else {
    echo "<br>Warning: Admin verification failed. Please check the database.";
}

$verify_stmt->close();
?> 