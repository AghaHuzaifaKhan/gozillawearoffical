<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Array of products with their corresponding image files
$products = [
    [
        'name' => 'Godzilla Classic Tee',
        'image' => 'Short-Sleeve-T-Shirt-Men-S-For-2021-Summer-Print-Black-White-Tshirt-Top-Tees-Classic-2-300x300.jpg'
    ],
    [
        'name' => 'Urban Monster Hoodie',
        'image' => 'rev_sliderhome22_model-4-1-e1731935780816-285x300.png'
    ],
    [
        'name' => 'Kaiju Cap',
        'image' => 'WhatsApp-Image-2024-11-14-at-03.31.46-300x300.jpeg'
    ],
    [
        'name' => 'Godzilla Street Wear Tee',
        'image' => 'rev_sliderhome22_t-shirt-1-300x300.png'
    ],
    [
        'name' => 'Monster City Hoodie',
        'image' => 'WhatsApp-Image-2024-11-25-at-01.39.14-300x300.jpeg'
    ]
];

echo "<h1>Updating Product Images</h1>";

foreach ($products as $product) {
    $name = mysqli_real_escape_string($conn, $product['name']);
    $image_path = 'assets/images/products/' . mysqli_real_escape_string($conn, $product['image']);
    
    $query = "UPDATE products SET image_url = '$image_path' WHERE name = '$name'";
    
    if (mysqli_query($conn, $query)) {
        echo "Updated image for {$product['name']}<br>";
    } else {
        echo "Error updating {$product['name']}: " . mysqli_error($conn) . "<br>";
    }
}

// Add new products with remaining images
$new_products = [
    [
        'name' => 'Black Urban Tee',
        'slug' => 'black-urban-tee',
        'description' => 'Sleek black urban t-shirt with modern design',
        'price' => 2100.00,
        'stock' => 50,
        'category_id' => 1,
        'image' => 'IMG_1141-300x300.jpg'
    ],
    [
        'name' => 'Street Style Hoodie',
        'slug' => 'street-style-hoodie',
        'description' => 'Comfortable street style hoodie for everyday wear',
        'price' => 4200.00,
        'stock' => 30,
        'category_id' => 2,
        'image' => 'IMG_1146-300x300.jpg'
    ],
    [
        'name' => 'Urban Camo Tee',
        'slug' => 'urban-camo-tee',
        'description' => 'Urban camouflage pattern t-shirt',
        'price' => 2300.00,
        'stock' => 45,
        'category_id' => 1,
        'image' => 'IMG_1183-300x300.png'
    ]
];

echo "<h2>Adding New Products</h2>";

foreach ($new_products as $product) {
    $name = mysqli_real_escape_string($conn, $product['name']);
    $slug = mysqli_real_escape_string($conn, $product['slug']);
    $description = mysqli_real_escape_string($conn, $product['description']);
    $image_path = 'assets/images/products/' . mysqli_real_escape_string($conn, $product['image']);
    
    $query = "INSERT INTO products (name, slug, description, price, stock, category_id, image_url) 
              VALUES ('$name', '$slug', '$description', {$product['price']}, {$product['stock']}, 
                      {$product['category_id']}, '$image_path')";
    
    if (mysqli_query($conn, $query)) {
        echo "Added new product: {$product['name']}<br>";
    } else {
        echo "Error adding {$product['name']}: " . mysqli_error($conn) . "<br>";
    }
}

echo "<p>Done! <a href='index.php'>Return to homepage</a></p>";
?> 