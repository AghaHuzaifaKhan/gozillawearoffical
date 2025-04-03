<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_order_confirmation($order_id, $user_email) {
    global $conn;
    
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
    
    // Create email content
    $subject = "Order Confirmation - Order #" . $order_id;
    $body = "
        <h2>Thank you for your order!</h2>
        <p>Dear {$order['username']},</p>
        <p>Your order has been confirmed. Here are your order details:</p>
        
        <h3>Order Information</h3>
        <p>Order ID: #{$order_id}</p>
        <p>Order Date: " . date('F j, Y', strtotime($order['created_at'])) . "</p>
        
        <h3>Order Items</h3>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <th style='text-align: left; padding: 10px; border-bottom: 1px solid #ddd;'>Product</th>
                <th style='text-align: right; padding: 10px; border-bottom: 1px solid #ddd;'>Quantity</th>
                <th style='text-align: right; padding: 10px; border-bottom: 1px solid #ddd;'>Price</th>
                <th style='text-align: right; padding: 10px; border-bottom: 1px solid #ddd;'>Total</th>
            </tr>
    ";
    
    foreach ($items as $item) {
        $body .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>{$item['name']}</td>
                <td style='text-align: right; padding: 10px; border-bottom: 1px solid #ddd;'>{$item['quantity']}</td>
                <td style='text-align: right; padding: 10px; border-bottom: 1px solid #ddd;'>" . format_price($item['price']) . "</td>
                <td style='text-align: right; padding: 10px; border-bottom: 1px solid #ddd;'>" . format_price($item['price'] * $item['quantity']) . "</td>
            </tr>
        ";
    }
    
    $body .= "
        </table>
        
        <h3>Order Summary</h3>
        <p>Subtotal: " . format_price($order['total_amount']) . "</p>
        <p>Shipping: " . format_price($order['shipping_cost']) . "</p>
        <p><strong>Total: " . format_price($order['total_amount'] + $order['shipping_cost']) . "</strong></p>
        
        <h3>Shipping Address</h3>
        <p>{$order['shipping_address']}</p>
        <p>{$order['shipping_city']}, {$order['shipping_state']} {$order['shipping_postal_code']}</p>
        <p>{$order['shipping_country']}</p>
        
        <p>If you have any questions, please contact our support team.</p>
        <p>Thank you for shopping with us!</p>
    ";
    
    return send_email($user_email, $subject, $body);
}

function send_password_reset($user_email, $reset_token) {
    $subject = "Password Reset Request";
    $reset_link = SITE_URL . "/reset_password.php?token=" . $reset_token;
    
    $body = "
        <h2>Password Reset Request</h2>
        <p>You have requested to reset your password. Click the link below to proceed:</p>
        <p><a href='{$reset_link}'>{$reset_link}</a></p>
        <p>If you didn't request this, please ignore this email.</p>
        <p>This link will expire in 1 hour.</p>
    ";
    
    return send_email($user_email, $subject, $body);
}

function send_email($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
} 