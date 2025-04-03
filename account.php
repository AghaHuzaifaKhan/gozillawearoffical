<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = null;
$error = '';

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing user query: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    error_log($e->getMessage());
}

// Get order history
$orders = [];
try {
    $query = "
        SELECT o.id, o.user_id, o.total_amount, o.status, o.shipping_address, o.created_at,
               GROUP_CONCAT(p.name) as product_names,
               GROUP_CONCAT(oi.quantity) as quantities
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id, o.user_id, o.total_amount, o.status, o.shipping_address, o.created_at
        ORDER BY o.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparing order history query: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $result->free();
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching order history: " . $e->getMessage());
}

// Handle profile update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email is already taken";
        }
        $stmt->close();

        // Handle password change if requested
        if (!empty($current_password)) {
            if (!verify_password($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            }
            if (empty($new_password)) {
                $errors[] = "New password is required";
            }
            if ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match";
            }
            if (strlen($new_password) < 8) {
                $errors[] = "New password must be at least 8 characters long";
            }
        }

        if (empty($errors)) {
            try {
                if (!empty($new_password)) {
                    $hashed_password = hash_password($new_password);
                    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
                }

                if ($stmt->execute()) {
                    $message = '<div class="alert success">Profile updated successfully!</div>';
                    // Refresh user data
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    $stmt->close();
                } else {
                    throw new Exception("Error updating profile");
                }
            } catch (Exception $e) {
                $message = '<div class="alert error">Error updating profile: ' . $e->getMessage() . '</div>';
                error_log($e->getMessage());
            }
        } else {
            $message = '<div class="alert error"><ul><li>' . implode('</li><li>', $errors) . '</li></ul></div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="account-container">
            <h1>My Account</h1>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php echo $message; ?>

            <div class="account-grid">
                <section class="profile-section">
                    <h2>Profile Information</h2>
                    <form class="profile-form" method="POST" action="">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <h3>Change Password</h3>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn">Update Profile</button>
                    </form>
                </section>

                <section class="orders-section">
                    <h2>Order History</h2>
                    <?php if (empty($orders)): ?>
                        <p class="no-orders">You haven't placed any orders yet.</p>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <span class="order-number">Order #<?php echo $order['id']; ?></span>
                                        <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-details">
                                        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                                        <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                        <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                        <?php if ($order['product_names']): ?>
                                            <p><strong>Items:</strong></p>
                                            <ul class="order-items">
                                                <?php 
                                                $products = explode(',', $order['product_names']);
                                                $quantities = explode(',', $order['quantities']);
                                                for ($i = 0; $i < count($products); $i++): 
                                                ?>
                                                    <li><?php echo $quantities[$i] . 'x ' . $products[$i]; ?></li>
                                                <?php endfor; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
    .account-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .account-container h1 {
        text-align: center;
        margin-bottom: 2rem;
    }

    .account-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
    }

    .profile-section,
    .orders-section {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .profile-section h2,
    .orders-section h2 {
        margin-bottom: 1.5rem;
        color: var(--primary-color);
    }

    .profile-section h3 {
        margin: 2rem 0 1rem;
        color: var(--primary-color);
        font-size: 1.2rem;
    }

    .profile-form .form-group {
        margin-bottom: 1.5rem;
    }

    .profile-form label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    .profile-form input,
    .profile-form textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .orders-list {
        display: grid;
        gap: 1.5rem;
    }

    .order-card {
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
    }

    .order-header {
        background: var(--primary-color);
        color: white;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-details {
        padding: 1rem;
    }

    .order-items {
        list-style: none;
        padding-left: 1rem;
        margin-top: 0.5rem;
    }

    .order-items li {
        margin-bottom: 0.25rem;
    }

    .no-orders {
        text-align: center;
        color: #666;
        padding: 2rem;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 2rem;
        border-radius: 5px;
        text-align: center;
    }

    .alert.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .alert li {
        margin-bottom: 0.5rem;
    }

    .alert li:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .account-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html> 