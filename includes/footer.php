<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About Godzilla Wear</h3>
            <p>Premium street fashion inspired by urban culture and style. Quality clothing for those who dare to stand out.</p>
        </div>
        
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="terms.php">Terms & Conditions</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Connect With Us</h3>
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        
        <div class="footer-section">
            <h3>Newsletter</h3>
            <form action="newsletter.php" method="POST" class="newsletter-form">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" class="btn">Subscribe</button>
            </form>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Godzilla Wear. All rights reserved.</p>
    </div>
</footer>

<style>
.main-footer {
    background: var(--primary-color);
    color: white;
    padding: 4rem 2rem 1rem;
    margin-top: 4rem;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.footer-section h3 {
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.footer-section ul {
    list-style: none;
}

.footer-section ul li {
    margin-bottom: 0.5rem;
}

.footer-section a {
    color: white;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section a:hover {
    color: var(--secondary-color);
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-link {
    font-size: 1.5rem;
}

.newsletter-form {
    display: flex;
    gap: 0.5rem;
}

.newsletter-form input {
    padding: 0.5rem;
    border: none;
    border-radius: 5px;
    flex-grow: 1;
}

.footer-bottom {
    text-align: center;
    margin-top: 3rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
}
</style> 