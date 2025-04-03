<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['order_id'])) {
    header('Location: shop.php');
    exit();
}

$order_id = $_SESSION['order_id'];

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Clear order_id from session
unset($_SESSION['order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="order-confirmation-container">
            <div class="confirmation-popup">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been confirmed and will be processed shortly.</p>
                
                <div class="order-details">
                    <h2>Order Details</h2>
                    <p>Order ID: #<?php echo $order_id; ?></p>
                    <p>Order Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    
                    <div class="order-items">
                        <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="order-item-image">
                                
                                <div class="order-item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                    <p>Price: <?php echo format_price($item['price']); ?></p>
                                    <p>Total: <?php echo format_price($item['price'] * $item['quantity']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span><?php echo format_price($order['total_amount']); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Shipping</span>
                            <span><?php echo format_price($order['shipping_cost']); ?></span>
                        </div>
                        
                        <div class="summary-item total">
                            <span>Total</span>
                            <span><?php echo format_price($order['total_amount'] + $order['shipping_cost']); ?></span>
                        </div>
                    </div>
                    
                    <div class="shipping-details">
                        <h3>Shipping Address</h3>
                        <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                        <p><?php echo htmlspecialchars($order['shipping_city']); ?>, 
                           <?php echo htmlspecialchars($order['shipping_state']); ?> 
                           <?php echo htmlspecialchars($order['shipping_postal_code']); ?></p>
                        <p><?php echo htmlspecialchars($order['shipping_country']); ?></p>
                    </div>
                </div>
                
                <div class="confirmation-actions">
                    <a href="shop.php" class="btn continue-shopping">
                        <i class="fas fa-shopping-bag"></i>
                        Continue Shopping
                    </a>
                    <a href="account.php" class="btn view-orders">
                        <i class="fas fa-list"></i>
                        View Orders
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 