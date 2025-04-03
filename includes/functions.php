<?php
// Security functions
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// User functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_by_id($user_id) {
    global $conn;
    $user_id = sanitize($user_id);
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Product functions
function get_featured_products($limit = 4) {
    global $conn;
    $limit = (int)$limit;
    $query = "SELECT * FROM products WHERE featured = 1 LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $products = [];
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    return $products;
}

function get_product_by_slug($slug) {
    global $conn;
    $slug = sanitize($slug);
    $query = "SELECT * FROM products WHERE slug = '$slug'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Cart functions
function get_cart_items($user_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image_url, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function add_to_cart($user_id, $product_id, $quantity = 1) {
    global $conn;
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("User not found: " . $user_id);
        return false;
    }
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product || $product['stock'] < $quantity) {
        error_log("Product not found or insufficient stock: " . $product_id);
        return false;
    }
    
    try {
        // Check if item already in cart
        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        }
        
        return $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        error_log("Database error in add_to_cart: " . $e->getMessage());
        return false;
    }
}

function update_cart_item($user_id, $product_id, $quantity) {
    global $conn;
    
    // Check if product has enough stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product || $product['stock'] < $quantity) {
        return false;
    }
    
    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    }
    
    return $stmt->execute();
}

function remove_from_cart($user_id, $product_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function clear_cart($user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function get_cart_total() {
    global $conn;
    $total = 0;
    
    if(isset($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $product_id => $quantity) {
            $query = "SELECT price FROM products WHERE id = " . (int)$product_id;
            $result = mysqli_query($conn, $query);
            $product = mysqli_fetch_assoc($result);
            $total += $product['price'] * $quantity;
        }
    }
    
    return $total;
}

// Format functions
function format_price($price) {
    return 'PKR ' . number_format($price, 0);
}

// URL functions
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

// Message functions
function set_message($message, $type = 'success') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

function get_message() {
    if(isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

// Admin functions
function is_admin() {
    global $conn;
    if (!is_logged_in()) {
        error_log("User not logged in during admin check");
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("Error preparing admin check query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        error_log("Error executing admin check query: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Debug information
    error_log("User ID: " . $user_id);
    error_log("Admin status: " . ($user['is_admin'] ?? 'not set'));
    
    // Explicit boolean check
    return isset($user['is_admin']) && $user['is_admin'] == 1;
}
?> 