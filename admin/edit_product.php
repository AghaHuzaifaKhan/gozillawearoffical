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

$errors = [];
$success = false;

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validate input
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    if (empty($slug)) {
        $errors[] = "Product slug is required";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative";
    }
    
    // Check if slug exists for other products
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
    $check_stmt->bind_param("si", $slug, $product_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "A product with this slug already exists";
    }
    
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE products SET name = ?, slug = ?, description = ?, price = ?, stock = ?, category_id = ?, featured = ? WHERE id = ?");
        $update_stmt->bind_param("sssdiiii", $name, $slug, $description, $price, $stock, $category_id, $featured, $product_id);
        
        if ($update_stmt->execute()) {
            $success = true;
            // Refresh product data
            $stmt->execute();
            $product = $result->fetch_assoc();
        } else {
            $errors[] = "Error updating product: " . $update_stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Edit Product</h1>
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
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Product updated successfully!
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($product['slug']); ?>" required>
                        <small>URL-friendly version of the product name</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (PKR)</label>
                        <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            Featured Product
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 