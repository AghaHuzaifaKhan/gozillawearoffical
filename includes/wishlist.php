<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

function add_to_wishlist($user_id, $product_id) {
    global $conn;
    
    // Check if product exists
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Product not found'];
    }
    
    // Add to wishlist
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Added to wishlist'];
    }
    
    return ['success' => false, 'message' => 'Failed to add to wishlist'];
}

function remove_from_wishlist($user_id, $product_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Removed from wishlist'];
    }
    
    return ['success' => false, 'message' => 'Failed to remove from wishlist'];
}

function get_wishlist($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, w.created_at as added_at 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function is_in_wishlist($user_id, $product_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
} 