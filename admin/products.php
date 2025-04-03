<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in()) {
    header('Location: /gozillawear/login.php');
    exit();
}

if (!is_admin()) {
    header('Location: /gozillawear/account.php');
    exit();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    $delete_query = "DELETE FROM products WHERE id = $product_id";
    if (mysqli_query($conn, $delete_query)) {
        set_message('Product deleted successfully');
    } else {
        set_message('Error deleting product', 'error');
    }
    header('Location: products.php');
    exit();
}

// Get all products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_pages = ceil($total_products / $per_page);

$products_query = "SELECT * FROM products ORDER BY created_at DESC LIMIT $offset, $per_page";
$products = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Manage Products</h1>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="products.php" class="active">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="upload_image.php">Upload Images</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <div class="admin-content">
            <?php if ($message = get_message()): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>

            <div class="admin-actions">
                <a href="add_product.php" class="btn">Add New Product</a>
            </div>

            <div class="admin-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <?php if ($product['image_url']): ?>
                                        <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                        <span class="no-image">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo format_price($product['price']); ?></td>
                                <td>
                                    <span class="stock-badge stock-<?php echo $product['stock'] < 5 ? 'low' : ($product['stock'] < 10 ? 'medium' : 'high'); ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['featured']): ?>
                                        <span class="status-badge status-delivered">Yes</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-small">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="delete_product" class="btn-small btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 