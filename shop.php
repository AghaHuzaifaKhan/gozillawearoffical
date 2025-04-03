<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle filtering and sorting
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name_asc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";

if ($category) {
    $query .= " AND c.slug = '$category'";
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

// Add pagination
$query .= " LIMIT $per_page OFFSET $offset";

// Get products
$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="shop-container">
            <aside class="filters">
                <h2>Filters</h2>
                <div class="filter-section">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="shop.php" <?php echo !$category ? 'class="active"' : ''; ?>>All Categories</a></li>
                        <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <li>
                                <a href="shop.php?category=<?php echo $cat['slug']; ?>" 
                                   <?php echo $category === $cat['slug'] ? 'class="active"' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="filter-section">
                    <h3>Sort By</h3>
                    <select name="sort" id="sort" onchange="window.location.href=this.value">
                        <option value="shop.php?sort=name_asc<?php echo $category ? '&category='.$category : ''; ?>" 
                                <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="shop.php?sort=name_desc<?php echo $category ? '&category='.$category : ''; ?>"
                                <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="shop.php?sort=price_asc<?php echo $category ? '&category='.$category : ''; ?>"
                                <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="shop.php?sort=price_desc<?php echo $category ? '&category='.$category : ''; ?>"
                                <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </div>
            </aside>

            <div class="products-section">
                <div class="product-grid">
                    <?php foreach($products as $product): ?>
                        <?php include 'includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html> 