<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Check if slug already exists
    $check_query = "SELECT id FROM products WHERE slug = '$slug'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        set_message('A product with this name already exists', 'error');
    } else {
        $insert_query = "INSERT INTO products (name, slug, description, price, stock, featured) 
                        VALUES ('$name', '$slug', '$description', $price, $stock, $featured)";
        
        if (mysqli_query($conn, $insert_query)) {
            set_message('Product added successfully');
            header('Location: products.php');
            exit();
        } else {
            set_message('Error adding product: ' . mysqli_error($conn), 'error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Add New Product</h1>
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

            <div class="admin-card">
                <form method="POST" class="product-form">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (PKR)</label>
                            <input type="number" id="price" name="price" min="0" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" id="featured">
                            Featured Product
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Add Product</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 