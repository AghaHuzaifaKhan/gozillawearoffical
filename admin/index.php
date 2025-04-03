<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in()) {
    header('Location: /gozillawear/login.php');
    exit();
}

if (!is_admin()) {
    header('Location: /gozillawear/account.php');
    exit();
}

// Get statistics
$stats = [
    'products' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'],
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
    'orders' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'],
    'pending_orders' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count']
];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.username 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC 
                       LIMIT 5";
$recent_orders = mysqli_query($conn, $recent_orders_query);

// Get low stock products
$low_stock_query = "SELECT * FROM products WHERE stock < 5 ORDER BY stock ASC LIMIT 5";
$low_stock_products = mysqli_query($conn, $low_stock_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Godzilla Wear</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <nav class="admin-nav">
                <a href="index.php" class="active">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="upload_image.php">Upload Images</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <div class="admin-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p class="stat-number"><?php echo $stats['products']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $stats['users']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $stats['orders']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Orders</h3>
                    <p class="stat-number"><?php echo $stats['pending_orders']; ?></p>
                </div>
            </div>

            <div class="admin-grid">
                <div class="admin-card">
                    <h2>Recent Orders</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
                                    <td><?php echo format_price($order['total_amount']); ?></td>
                                    <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-card">
                    <h2>Low Stock Products</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = mysqli_fetch_assoc($low_stock_products)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><span class="stock-badge stock-low"><?php echo $product['stock']; ?></span></td>
                                    <td><a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-small">Update</a></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 