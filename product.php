<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get product slug from URL
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

// Get product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// If product not found, redirect to shop
if (!$product) {
    header('Location: shop.php');
    exit();
}

// Get related products
$related_stmt = $conn->prepare("SELECT * FROM products 
                              WHERE category_id = ? AND id != ? 
                              LIMIT 4");
$related_stmt->bind_param("ii", $product['category_id'], $product['id']);
$related_stmt->execute();
$related_products = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="product-detail-container">
            <div class="product-detail-grid">
                <div class="product-image-container">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-detail-image">
                </div>
                
                <div class="product-info-container">
                    <nav class="breadcrumb">
                        <a href="index.php">Home</a>
                        <span class="separator">/</span>
                        <a href="shop.php">Shop</a>
                        <span class="separator">/</span>
                        <a href="shop.php?category=<?php echo urlencode($product['category_name']); ?>">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                        <span class="separator">/</span>
                        <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
                    </nav>

                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-price">
                        <?php echo format_price($product['price']); ?>
                    </div>

                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <form action="cart.php" method="POST" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="quantity-selector">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            </div>
                            <button type="submit" class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="out-of-stock">
                            <i class="fas fa-times-circle"></i>
                            Out of Stock
                        </div>
                    <?php endif; ?>

                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Stock:</span>
                            <span class="meta-value"><?php echo $product['stock']; ?> units</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($related_products->num_rows > 0): ?>
                <div class="related-products">
                    <h2>Related Products</h2>
                    <div class="product-grid">
                        <?php while ($related = $related_products->fetch_assoc()): ?>
                            <div class="product-card">
                                <a href="product.php?slug=<?php echo $related['slug']; ?>">
                                    <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                         class="product-image">
                                    <div class="product-info">
                                        <h3 class="product-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                                        <div class="product-price"><?php echo format_price($related['price']); ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 