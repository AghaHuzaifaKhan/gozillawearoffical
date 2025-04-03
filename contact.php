<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message_content = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if ($name && $email && $subject && $message_content) {
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$name, $email, $subject, $message_content])) {
            $message = '<div class="alert success">Thank you for your message! We will get back to you soon.</div>';
        } else {
            $message = '<div class="alert error">Sorry, there was an error sending your message. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert error">Please fill in all fields.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Godzilla Wear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="contact-container">
            <section class="contact-hero">
                <h1>Contact Us</h1>
                <p>We'd love to hear from you! Send us a message and we'll respond as soon as possible.</p>
            </section>

            <?php echo $message; ?>

            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Visit Us</h3>
                        <p>123 Street Fashion Ave<br>Style District<br>City, ST 12345</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-phone"></i>
                        <h3>Call Us</h3>
                        <p>Phone: (555) 123-4567<br>Fax: (555) 123-4568</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-envelope"></i>
                        <h3>Email Us</h3>
                        <p>info@godzillawear.com<br>support@godzillawear.com</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-clock"></i>
                        <h3>Business Hours</h3>
                        <p>Monday - Friday: 9AM - 6PM<br>Saturday: 10AM - 4PM<br>Sunday: Closed</p>
                    </div>
                </div>

                <form class="contact-form" method="POST" action="">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>

            <div class="map-container">
                <h2>Find Us</h2>
                <div class="map">
                    <!-- Replace with actual map embed code -->
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.1!2d-73.9!3d40.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzAwLjAiTiA3M8KwNTQnMDAuMCJX!5e0!3m2!1sen!2sus!4v1234567890"
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .contact-hero {
        text-align: center;
        margin-bottom: 3rem;
    }

    .contact-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .contact-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        margin-bottom: 3rem;
    }

    .contact-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .info-card {
        padding: 1.5rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
    }

    .info-card i {
        font-size: 2rem;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .contact-form {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .map-container {
        margin-top: 3rem;
    }

    .map-container h2 {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .map {
        border-radius: 10px;
        overflow: hidden;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 2rem;
        border-radius: 5px;
        text-align: center;
    }

    .alert.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .contact-content {
            grid-template-columns: 1fr;
        }

        .contact-info {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html> 