<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

function add_review($product_id, $user_id, $rating, $comment) {
    global $conn;
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Invalid rating value'];
    }
    
    // Check if user has already reviewed this product
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $product_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'You have already reviewed this product'];
    }
    
    // Add review
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    
    if ($stmt->execute()) {
        // Update product average rating
        update_product_rating($product_id);
        return ['success' => true, 'message' => 'Review added successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to add review'];
}

function get_product_reviews($product_id, $limit = 10) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT r.*, u.username, u.avatar_url 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT ?
    ");
    
    $stmt->bind_param("ii", $product_id, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function update_product_rating($product_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE products 
        SET rating = (
            SELECT AVG(rating) 
            FROM reviews 
            WHERE product_id = ?
        )
        WHERE id = ?
    ");
    
    $stmt->bind_param("ii", $product_id, $product_id);
    return $stmt->execute();
}

function delete_review($review_id, $user_id) {
    global $conn;
    
    // Check if user owns the review
    $check_stmt = $conn->prepare("SELECT product_id FROM reviews WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $review_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'You cannot delete this review'];
    }
    
    $product_id = $result->fetch_assoc()['product_id'];
    
    // Delete review
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $review_id, $user_id);
    
    if ($stmt->execute()) {
        // Update product average rating
        update_product_rating($product_id);
        return ['success' => true, 'message' => 'Review deleted successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to delete review'];
} 