<?php
if (!isset($product)) {
    return;
}
?>

<div class="product-card">
    <a href="product.php?slug=<?php echo $product['slug']; ?>">
        <img src="<?php echo $product['image_url'] ? $product['image_url'] : 'assets/images/placeholder.jpg'; ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>" 
             class="product-image">
        
        <div class="product-info">
            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="product-price"><?php echo format_price($product['price']); ?></p>
            
            <?php if($product['stock'] > 0): ?>
                <form action="cart.php" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                    <button type="submit" class="btn add-to-cart-btn">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <p class="out-of-stock">Out of Stock</p>
            <?php endif; ?>
        </div>
    </a>
</div> 