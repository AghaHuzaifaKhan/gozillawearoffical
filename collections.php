<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all categories with their products
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="collections-container">
            <h1>Our Collections</h1>
            
            <div class="collections-grid">
                <?php while($category = mysqli_fetch_assoc($result)): ?>
                    <div class="collection-card">
                        <div class="collection-info">
                            <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                            <p class="product-count"><?php echo $category['product_count']; ?> Products</p>
                            <a href="shop.php?category=<?php echo $category['slug']; ?>" class="btn">View Collection</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Featured Items Section -->
            <section class="featured-items">
                <h2>Featured Items</h2>
                <div class="product-grid">
                    <?php
                    $featured_query = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
                    $featured_result = mysqli_query($conn, $featured_query);
                    
                    while($product = mysqli_fetch_assoc($featured_result)) {
                        include 'includes/product-card.php';
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <style>
    .collections-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .collections-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }
    
    .collection-card {
        background: var(--background-color);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .collection-card:hover {
        transform: translateY(-5px);
    }
    
    .collection-info {
        padding: 2rem;
        text-align: center;
    }
    
    .collection-info h2 {
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    
    .product-count {
        color: var(--secondary-color);
        margin: 1rem 0;
        font-weight: bold;
    }
    
    .featured-items {
        margin-top: 4rem;
    }
    </style>
</body>
</html> 