<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: account.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username or email already exists
    $check_stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
    if (!$check_stmt) {
        $errors[] = "Database error occurred";
        error_log("Registration prepare error: " . $conn->error);
    } else {
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            while ($user = $check_result->fetch_assoc()) {
                if ($user['username'] === $username) {
                    $errors[] = "Username already taken";
                }
                if ($user['email'] === $email) {
                    $errors[] = "Email already registered";
                }
            }
        }
        $check_stmt->close();
    }
    
    if (empty($errors)) {
        $hashed_password = hash_password($password);
        
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
        if (!$insert_stmt) {
            $errors[] = "Database error occurred";
            error_log("Registration insert prepare error: " . $conn->error);
        } else {
            $insert_stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);
            
            if ($insert_stmt->execute()) {
                // Registration successful
                $_SESSION['user_id'] = $insert_stmt->insert_id;
                $_SESSION['username'] = $username;
                
                header('Location: account.php');
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
                error_log("Registration insert error: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="form-container">
            <h1>Create Account</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name">
                </div>
                
                <button type="submit" class="btn">Register</button>
            </form>
            
            <p class="form-footer">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 