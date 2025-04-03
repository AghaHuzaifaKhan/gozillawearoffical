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

// Handle user role update
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $update_query = "UPDATE users SET is_admin = $is_admin WHERE id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        set_message('User role updated successfully');
    } else {
        set_message('Error updating user role', 'error');
    }
    header('Location: users.php');
    exit();
}

// Get all users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_pages = ceil($total_users / $per_page);

$users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT $offset, $per_page";
$users = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Manage Users</h1>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php" class="active">Users</a>
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
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_admin" <?php echo $user['is_admin'] ? 'checked' : ''; ?> 
                                                   onchange="this.form.submit()" <?php echo $user['id'] === $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                            Admin
                                        </label>
                                        <input type="hidden" name="update_role" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn-small">View Details</a>
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