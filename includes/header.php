<header class="main-header">
    <nav class="nav-container">
        <div class="logo">
            <a href="index.php">
                <img src="assets/images/logo.png" alt="Godzilla Wear Logo">
            </a>
        </div>
        
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="collections.php">Collections</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <div class="nav-actions">
            <div class="search">
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search products...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="user-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="account-link">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                    <a href="logout.php" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="login-link">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                <?php endif; ?>
                
                <a href="cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart</span>
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>
</header>

<style>
.main-header {
    background: var(--background-color);
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
    height: var(--header-height);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    flex-shrink: 0;
}

.logo img {
    width: 250px;
    height: 100px;
    display: block;
    object-fit: contain;
}

.nav-links {
    display: flex;
    list-style: none;
    gap: 2rem;
    margin: 0;
    padding: 0;
}

.nav-links a {
    color: var(--text-color);
    text-decoration: none;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    transition: color 0.3s ease;
}

.nav-links a:hover {
    color: var(--secondary-color);
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.search form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search input {
    padding: 0.5rem 1rem;
    border: 1px solid var(--gray-medium);
    border-radius: 5px;
    width: 200px;
}

.search button {
    background: none;
    border: none;
    color: var(--text-color);
    cursor: pointer;
    padding: 0.5rem;
}

.user-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-actions a {
    color: var(--text-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.cart-count {
    background: var(--secondary-color);
    color: white;
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 50%;
}

@media (max-width: 1024px) {
    .search input {
        width: 150px;
    }
}

@media (max-width: 768px) {
    .nav-container {
        padding: 0 1rem;
    }

    .nav-links {
        display: none;
    }

    .search input {
        width: 120px;
    }

    .user-actions span {
        display: none;
    }
}
</style> 