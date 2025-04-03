<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="about-container">
            <section class="about-hero">
                <h1>About Godzilla Wear</h1>
                <p class="tagline">Unleashing Street Style Since 2024</p>
            </section>

            <section class="about-content">
                <div class="about-section">
                    <h2>Our Story</h2>
                    <p>Godzilla Wear was born from a passion for street culture and urban fashion. We believe that style is a form of self-expression, and our mission is to provide unique, high-quality streetwear that helps you make a statement.</p>
                </div>

                <div class="about-section">
                    <h2>Our Vision</h2>
                    <p>To be the leading streetwear brand that combines urban aesthetics with comfort and quality, creating clothing that empowers individuals to express their unique style with confidence.</p>
                </div>

                <div class="about-section values">
                    <h2>Our Values</h2>
                    <div class="values-grid">
                        <div class="value-card">
                            <i class="fas fa-star"></i>
                            <h3>Quality</h3>
                            <p>Premium materials and craftsmanship in every piece</p>
                        </div>
                        <div class="value-card">
                            <i class="fas fa-paint-brush"></i>
                            <h3>Creativity</h3>
                            <p>Unique designs that push boundaries</p>
                        </div>
                        <div class="value-card">
                            <i class="fas fa-heart"></i>
                            <h3>Authenticity</h3>
                            <p>True to street culture and urban lifestyle</p>
                        </div>
                        <div class="value-card">
                            <i class="fas fa-globe"></i>
                            <h3>Sustainability</h3>
                            <p>Committed to ethical and sustainable practices</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="team-section">
                <h2>Meet Our Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <img src="assets/images/team/founder.jpg" alt="Founder" onerror="this.src='assets/images/placeholder.jpg'">
                        <h3>John Doe</h3>
                        <p class="position">Founder & Creative Director</p>
                    </div>
                    <!-- Add more team members as needed -->
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
    .about-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .about-hero {
        text-align: center;
        margin-bottom: 4rem;
    }

    .about-hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .tagline {
        font-size: 1.2rem;
        color: var(--secondary-color);
    }

    .about-section {
        margin-bottom: 4rem;
    }

    .about-section h2 {
        margin-bottom: 1.5rem;
        color: var(--primary-color);
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .value-card {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .value-card i {
        font-size: 2rem;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .team-member {
        text-align: center;
    }

    .team-member img {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
    }

    .position {
        color: var(--secondary-color);
        font-style: italic;
    }

    @media (max-width: 768px) {
        .about-hero h1 {
            font-size: 2rem;
        }

        .values-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html> 