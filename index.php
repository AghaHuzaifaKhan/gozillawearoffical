<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Debug database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get featured products with debug info
$featured_query = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
$featured_result = mysqli_query($conn, $featured_query);

if (!$featured_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Debug product count
$product_count = mysqli_num_rows($featured_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Godzilla Wear - Street Fashion</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section with Slider -->
        <section class="hero">
            <div class="slider">
                <div class="slide active" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/sliders/slider1.jpg')">
                    <div class="hero-content">
                        <h1>GODZILLA WEAR</h1>
                        <p>Unleash Your Street Style</p>
                        <a href="shop.php" class="cta-button">Shop Now</a>
                    </div>
                </div>
                <div class="slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/sliders/slider2.jpg')">
                    <div class="hero-content">
                        <h1>NEW COLLECTION</h1>
                        <p>Discover the Latest Trends</p>
                        <a href="collections.php" class="cta-button">View Collection</a>
                    </div>
                </div>
                <div class="slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/sliders/slider3.jpg')">
                    <div class="hero-content">
                        <h1>URBAN STYLE</h1>
                        <p>Express Your Identity</p>
                        <a href="shop.php" class="cta-button">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="slider-nav">
                <span class="slider-dot active"></span>
                <span class="slider-dot"></span>
                <span class="slider-dot"></span>
            </div>
        </section>

        <!-- Debug Info -->
        <?php if (isset($_GET['debug'])): ?>
        <div style="background: #f0f0f0; padding: 20px; margin: 20px;">
            <h3>Debug Information:</h3>
            <p>Database Connected: <?php echo $conn ? 'Yes' : 'No'; ?></p>
            <p>Number of Featured Products: <?php echo $product_count; ?></p>
            <p>Database Name: <?php echo DB_NAME; ?></p>
        </div>
        <?php endif; ?>

        <!-- Featured Products -->
        <section class="featured-products">
            <h2>Featured Collections</h2>
            <div class="product-grid">
                <?php
                if ($product_count > 0) {
                    while($product = mysqli_fetch_assoc($featured_result)) {
                        include 'includes/product-card.php';
                    }
                } else {
                    echo '<p style="text-align: center; grid-column: 1/-1;">No featured products found. Please check the database.</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script>
    // Slider functionality
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        // Add click events to dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Auto advance slides
        setInterval(nextSlide, 5000);
    });
    </script>
</body>
</html> 