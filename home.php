<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch summary data
$totalBooksQuery = $pdo->query("SELECT COUNT(*) AS total FROM books");
$totalBooks = $totalBooksQuery->fetchColumn();

$totalBorrowedQuery = $pdo->query("SELECT COUNT(*) AS total FROM borrowers");
$totalBorrowed = $totalBorrowedQuery->fetchColumn();

$totalUsersQuery = $pdo->query("SELECT COUNT(*) AS total FROM ccai_user");
$totalUsers = $totalUsersQuery->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Home</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="nav-container">
        <button class="nav-toggle" onclick="toggleNav()">
            <i class="fas fa-bars"></i>
        </button>
        <br>
        <br>
        <img src="img/logo.png" alt="Logo" class="nav-logo">
        <br>
        <div class="nav-content">
            <a href="home.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="inventory.php" class="nav-link"><i class="fas fa-box"></i> Inventory</a>
            <a href="borrow.php" class="nav-link"><i class="fas fa-handshake"></i> Borrow</a>
            <a href="addition.php" class="nav-link"><i class="fas fa-plus"></i> Addition</a>
        </div>
        <div class="nav-footer">
            <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> User Settings</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="content-container">
        <div class="summary-container">
            <a href="inventory.php" class="summary-item">
                <i class="fas fa-book"></i>
                <div class="summary-text">
                    <h3>Total Books</h3>
                    <p><?php echo htmlspecialchars($totalBooks); ?></p>
                </div>
            </a>
            <a href="borrow.php" class="summary-item">
                <i class="fas fa-handshake"></i>
                <div class="summary-text">
                    <h3>Total Borrowed</h3>
                    <p><?php echo htmlspecialchars($totalBorrowed); ?></p>
                </div>
            </a>
            <a href="settings.php" class="summary-item">
                <i class="fas fa-users"></i>
                <div class="summary-text">
                    <h3>Total Users</h3>
                    <p><?php echo htmlspecialchars($totalUsers); ?></p>
                </div>
            </a>
            <a href="addition.php" class="summary-item">
                <i class="fas fa-plus"></i>
                <div class="summary-text">
                    <h3>Add Books</h3>
                </div>
            </a>
        </div>
    </div>

    <script>
        function toggleNav() {
            document.querySelector('.nav-container').classList.toggle('minimized');
            document.querySelector('.content-container').classList.toggle('expanded');
        }
    </script>
</body>

</html>