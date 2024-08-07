<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialize message variable
$message = '';
$messageType = '';

// Handle book addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['books'])) {
    $books = $_POST['books'];

    try {
        // Prepare SQL statement
        $stmt = $pdo->prepare("INSERT INTO books (book_category, book_name, author, isbn, year) VALUES (?, ?, ?, ?, ?)");

        // Iterate through each book and insert into the database
        foreach ($books as $index => $book) {
            $category = strtoupper(trim($book['category']));
            $name = strtoupper(trim($book['name']));
            $author = strtoupper(trim($book['author']));
            $isbn = strtoupper(trim($book['isbn']));
            $year = strtoupper(trim($book['year']));

            $result = $stmt->execute([$category, $name, $author, $isbn, $year]);
            if (!$result) {
                throw new Exception("Failed to insert data: " . implode(", ", $stmt->errorInfo()));
            }
        }
        
        $message = "<p>Books added successfully.</p>";
        $messageType = 'success';
    } catch (Exception $e) {
        $message = "<p>Error adding books: " . $e->getMessage() . "</p>";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Books</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/addition.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <div class="addition-form">
            <div class="header-container">
                <h2>Add Books</h2>
                <div class="header-controls">
                    <button type="button" id="add-row"><i class="fas fa-plus"></i> Add Row</button>
                    <button type="button" id="remove-row"><i class="fas fa-minus"></i> Remove Row</button>
                </div>
            </div>
            <?php if ($message) echo "<div class='notification $messageType'>$message</div>"; ?>

            <div class="row-count">
                Total Rows: <span id="row-count">1</span>
            </div>

            <form method="POST">
                <table id="books-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="books[0][category]"></td>
                            <td><input type="text" name="books[0][name]" required></td>
                            <td><input type="text" name="books[0][author]" required></td>
                            <td><input type="text" name="books[0][isbn]" required></td>
                            <td><input type="text" name="books[0][year]" required></td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <br>
                <button type="submit">Add Books</button>
            </form>
        </div>
    </div>

    <script>
        let rowIndex = 1;

        function updateRowCount() {
            const rowCount = document.querySelector('#books-table tbody').rows.length;
            document.getElementById('row-count').textContent = rowCount;
        }

        document.getElementById('add-row').addEventListener('click', function() {
            const tableBody = document.querySelector('#books-table tbody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td><input type="text" name="books[${rowIndex}][category]"></td>
                <td><input type="text" name="books[${rowIndex}][name]" required></td>
                <td><input type="text" name="books[${rowIndex}][author]" required></td>
                <td><input type="text" name="books[${rowIndex}][isbn]" required></td>
                <td><input type="text" name="books[${rowIndex}][year]" required></td>
            `;

            tableBody.appendChild(newRow);
            rowIndex++;
            updateRowCount();
        });

        document.getElementById('remove-row').addEventListener('click', function() {
            const tableBody = document.querySelector('#books-table tbody');
            if (tableBody.rows.length > 1) {
                tableBody.deleteRow(-1);
                rowIndex--;
                updateRowCount();
            }
        });

        // Function to hide success message after 5 seconds
        function hideNotification() {
            const notification = document.querySelector('.notification.success');
            if (notification) {
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }
        }
        hideNotification();

        // Initialize row count on page load
        updateRowCount();
    </script>
</body>
</html>
