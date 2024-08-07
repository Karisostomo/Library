<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle update, return, and delete actions
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    header("Location: borrow_form.php?edit=$id");
    exit;
} elseif (isset($_GET['return'])) {
    $id = intval($_GET['return']);
    $stmt = $pdo->prepare("UPDATE borrowers SET borrow_status = 'returned' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = 'The book has been marked as returned successfully.';
    header("Location: borrow.php");
    exit;
} elseif (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM borrowers WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = 'The book has been deleted successfully.';
    header("Location: borrow.php");
    exit;
}

// Handle search
$searchQuery = '';
$searchParams = [];
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    if (!empty($searchQuery)) {
        $searchParams = ['%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%'];
    }
}

$sql = "SELECT * FROM borrowers";
if ($searchQuery) {
    $sql .= " WHERE name LIKE ? OR b_class LIKE ? OR b_bookname LIKE ? OR borrow_status LIKE ?";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($searchParams);
$borrowers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Borrowers List</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/borrow.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmReturn(id) {
            if (confirm("Are you sure you want to mark this book as returned?")) {
                window.location.href = 'borrow.php?return=' + id;
            }
        }

        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                window.location.href = 'borrow.php?delete=' + id;
            }
        }
    </script>
</head>
<body>
    <div class="nav-container">    
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
        <div class="header-container">
            <?php if ($searchQuery): ?>
                <a href="borrow.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            <?php endif; ?>
            
            <a href="borrow_form.php" class="borrow-button">
                <i class="fas fa-plus"></i> Borrow
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                <a href="borrow.php" class="notification-btn">Back</a>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="search-container">
            <form method="GET" action="borrow.php" class="search-form">
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search..">
                <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="table-container">
            <h2>Borrowers List</h2>
            <br>
            <?php if (empty($borrowers)): ?>
                <p>No records found</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date Borrowed</th>
                            <th>Name of Borrower</th>
                            <th>ID Number</th>
                            <th>Classification</th>
                            <th>Book Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowers as $borrower): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($borrower['borrow_date']); ?></td>
                                <td><?php echo htmlspecialchars($borrower['name']); ?></td>
                                <td><?php echo htmlspecialchars($borrower['number']); ?></td>
                                <td><?php echo htmlspecialchars($borrower['b_class']); ?></td>
                                <td><?php echo htmlspecialchars($borrower['b_bookname']); ?></td>
                                <td><?php echo htmlspecialchars($borrower['borrow_status']); ?></td>
                                <td>
                                    <a href="javascript:void(0);" onclick="confirmReturn(<?php echo $borrower['id']; ?>);" class="action-icon" title="Mark as Return">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <a href="borrow_form.php?edit=<?php echo $borrower['id']; ?>" class="action-icon" title="Update">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $borrower['id']; ?>);" class="action-icon delete-icon" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
