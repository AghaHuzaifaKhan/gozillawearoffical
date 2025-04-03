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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['product_image'])) {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $file = $_FILES['product_image'];
        
        // Check if product exists
        $query = "SELECT * FROM products WHERE id = $product_id";
        $result = mysqli_query($conn, $query);
        $product = mysqli_fetch_assoc($result);
        
        if ($product) {
            // File upload configuration
            $target_dir = "../assets/images/products/";
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = $product['slug'] . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_extension, $allowed_types)) {
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                // Upload file
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    // Update product image URL in database
                    $image_url = 'assets/images/products/' . $new_filename;
                    $update_query = "UPDATE products SET image_url = '$image_url' WHERE id = $product_id";
                    
                    if (mysqli_query($conn, $update_query)) {
                        set_message('Image uploaded successfully!');
                    } else {
                        set_message('Error updating database.', 'error');
                    }
                } else {
                    set_message('Error uploading file.', 'error');
                }
            } else {
                set_message('Invalid file type. Allowed types: JPG, JPEG, PNG, GIF', 'error');
            }
        } else {
            set_message('Product not found.', 'error');
        }
    }
}

// Get all products
$products_query = "SELECT id, name, image_url FROM products ORDER BY name";
$products_result = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product Images - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .upload-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .upload-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .product-list {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .product-card {
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            padding: 0.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }

        .product-card .no-image {
            width: 100%;
            height: 150px;
            background: var(--gray-light);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-medium);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .product-card h3 {
            margin: 0;
            font-size: 0.875rem;
            color: var(--text-color);
        }

        .file-upload {
            border: 2px dashed var(--gray-medium);
            border-radius: 5px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .file-upload:hover {
            border-color: var(--secondary-color);
            background: #fff5f5;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-upload label {
            display: block;
            cursor: pointer;
        }

        .file-upload .upload-icon {
            font-size: 2rem;
            color: var(--gray-medium);
            margin-bottom: 0.5rem;
        }

        .file-upload .upload-text {
            color: var(--gray-medium);
            margin-bottom: 0.5rem;
        }

        .file-upload .file-name {
            color: var(--secondary-color);
            font-weight: 500;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin: 1rem auto;
            display: none;
        }

        .preview-image.show {
            display: block;
        }

        @media (max-width: 768px) {
            .upload-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Upload Product Images</h1>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="upload_image.php" class="active">Upload Images</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <div class="admin-content">
            <?php if ($message = get_message()): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>

            <div class="upload-container">
                <div class="upload-form">
                    <h2>Upload New Image</h2>
                    <form action="upload_image.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="product_id">Select Product:</label>
                            <select name="product_id" id="product_id" required>
                                <option value="">Choose a product...</option>
                                <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                    <option value="<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                        <?php echo $product['image_url'] ? ' (Has Image)' : ' (No Image)'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="file-upload">
                            <input type="file" name="product_image" id="product_image" accept="image/*" required>
                            <label for="product_image">
                                <div class="upload-icon">📁</div>
                                <div class="upload-text">Click to upload or drag and drop</div>
                                <div class="file-name">No file chosen</div>
                            </label>
                        </div>

                        <img id="preview" class="preview-image" alt="Preview">

                        <button type="submit" class="btn">Upload Image</button>
                    </form>
                </div>

                <div class="product-list">
                    <h2>Product Images</h2>
                    <div class="product-grid">
                        <?php 
                        mysqli_data_seek($products_result, 0);
                        while ($product = mysqli_fetch_assoc($products_result)): 
                        ?>
                            <div class="product-card">
                                <?php if ($product['image_url']): ?>
                                    <img src="../<?php echo $product['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">No image</div>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File upload preview
        document.getElementById('product_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview');
            const fileName = document.querySelector('.file-name');
            
            if (file) {
                fileName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.add('show');
                }
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'No file chosen';
                preview.classList.remove('show');
            }
        });

        // Drag and drop functionality
        const fileUpload = document.querySelector('.file-upload');
        const fileInput = document.getElementById('product_image');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUpload.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileUpload.classList.add('highlight');
        }

        function unhighlight(e) {
            fileUpload.classList.remove('highlight');
        }

        fileUpload.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            // Trigger the change event to show preview
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    </script>
</body>
</html> 