<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if (!is_logged_in()) {
            $_SESSION['error'] = "Please log in to manage your cart";
            header('Location: login.php');
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Verify user exists in database
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "User account not found. Please log in again.";
            session_destroy();
            header('Location: login.php');
            exit();
        }
        
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                    $product_id = (int)$_POST['product_id'];
                    $quantity = (int)$_POST['quantity'];
                    
                    if ($quantity <= 0) {
                        $_SESSION['error'] = "Invalid quantity";
                        header('Location: cart.php');
                        exit();
                    }
                    
                    if (add_to_cart($user_id, $product_id, $quantity)) {
                        $_SESSION['success'] = "Item added to cart";
                    } else {
                        $_SESSION['error'] = "Failed to add item to cart. Please try again.";
                    }
                }
                break;
                
            case 'update':
                if (isset($_POST['quantity']) && isset($_POST['product_id'])) {
                    $product_id = (int)$_POST['product_id'];
                    $quantity = (int)$_POST['quantity'];
                    
                    if (update_cart_item($user_id, $product_id, $quantity)) {
                        $_SESSION['success'] = "Cart updated";
                    } else {
                        $_SESSION['error'] = "Failed to update cart";
                    }
                }
                break;
                
            case 'remove':
                if (isset($_POST['product_id'])) {
                    $product_id = (int)$_POST['product_id'];
                    
                    if (remove_from_cart($user_id, $product_id)) {
                        $_SESSION['success'] = "Item removed from cart";
                    } else {
                        $_SESSION['error'] = "Failed to remove item from cart";
                    }
                }
                break;
                
            case 'checkout':
                // Process checkout
                $cart_items = get_cart_items($user_id);
                
                if (empty($cart_items)) {
                    $_SESSION['error'] = "Your cart is empty";
                    header('Location: cart.php');
                    exit();
                }
                
                // Calculate totals
                $subtotal = 0;
                foreach ($cart_items as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
                
                $shipping_cost = 10.00; // Fixed shipping cost
                $total = $subtotal + $shipping_cost;
                
                // Create order
                $stmt = $conn->prepare("
                    INSERT INTO orders (user_id, total_amount, shipping_cost, status, 
                    shipping_address, shipping_city, shipping_state, shipping_postal_code, 
                    shipping_country) 
                    VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param("iddsssss", 
                    $user_id, 
                    $subtotal, 
                    $shipping_cost,
                    $_POST['shipping_address'],
                    $_POST['shipping_city'],
                    $_POST['shipping_state'],
                    $_POST['shipping_postal_code'],
                    $_POST['shipping_country']
                );
                
                if ($stmt->execute()) {
                    $order_id = $conn->insert_id;
                    
                    // Add order items
                    $stmt = $conn->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES (?, ?, ?, ?)
                    ");
                    
                    foreach ($cart_items as $item) {
                        $stmt->bind_param("iiid", 
                            $order_id, 
                            $item['product_id'], 
                            $item['quantity'], 
                            $item['price']
                        );
                        $stmt->execute();
                        
                        // Update product stock
                        update_product_stock($item['product_id'], $item['quantity']);
                    }
                    
                    // Clear cart
                    clear_cart($user_id);
                    
                    // Send order confirmation email
                    $user_email = $_SESSION['email'];
                    send_order_confirmation($order_id, $user_email);
                    
                    // Set success message and redirect
                    $_SESSION['order_id'] = $order_id;
                    header('Location: order_confirmation.php');
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to process order";
                    header('Location: cart.php');
                    exit();
                }
                break;
        }
        
        header('Location: cart.php');
        exit();
    }
}

// Get cart items
$cart_items = [];
$subtotal = 0;
$shipping_cost = 10.00;

if (is_logged_in()) {
    $cart_items = get_cart_items($_SESSION['user_id']);
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}

$total = $subtotal + $shipping_cost;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="cart-container">
            <h1>Shopping Cart</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty</p>
                    <a href="shop.php" class="btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-grid">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="cart-item-image">
                                
                                <div class="cart-item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="cart-item-price">
                                        <?php echo format_price($item['price']); ?>
                                    </div>
                                </div>
                                
                                <div class="cart-item-quantity">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>" 
                                               onchange="this.form.submit()">
                                    </form>
                                </div>
                                
                                <div class="cart-item-total">
                                    <?php echo format_price($item['price'] * $item['quantity']); ?>
                                </div>
                                
                                <form method="POST" class="remove-item-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="remove-item-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span><?php echo format_price($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Shipping</span>
                            <span><?php echo format_price($shipping_cost); ?></span>
                        </div>
                        
                        <div class="summary-item total">
                            <span>Total</span>
                            <span><?php echo format_price($total); ?></span>
                        </div>
                        
                        <form method="POST" class="checkout-form">
                            <input type="hidden" name="action" value="checkout">
                            
                            <div class="form-group">
                                <label for="shipping_address">Shipping Address</label>
                                <textarea id="shipping_address" name="shipping_address" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_city">City</label>
                                    <input type="text" id="shipping_city" name="shipping_city" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shipping_state">State</label>
                                    <input type="text" id="shipping_state" name="shipping_state" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_postal_code">Postal Code</label>
                                    <input type="text" id="shipping_postal_code" name="shipping_postal_code" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shipping_country">Country</label>
                                    <input type="text" id="shipping_country" name="shipping_country" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="checkout-btn">
                                <i class="fas fa-lock"></i>
                                Proceed to Checkout
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 