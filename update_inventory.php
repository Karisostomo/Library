<?php
session_start();
require 'config.php';

// // Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit;
// }

// Fetch book details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $bookCategory = $_POST['book_category'];
    $bookName = $_POST['book_name'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $year = $_POST['year'];

    try {
        $stmt = $pdo->prepare("UPDATE books SET book_category = :book_category, book_name = :book_name, author = :author, isbn = :isbn, year = :year WHERE id = :id");
        $stmt->bindParam(':book_category', $bookCategory);
        $stmt->bindParam(':book_name', $bookName);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        header('Location: inventory.php?notification=updated');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Update Book</title>
    <link rel="stylesheet" type="text/css" href="css/inventory.css">
</head>

<body>
    <div class="nav-container">
        <!-- Navigation Content Here -->
    </div>
    <div class="dashboard-container">
        <h1>Update Book</h1>
        <form method="POST">
            <label>Category:</label>
            <input type="text" name="book_category" value="<?php echo htmlspecialchars($book['book_category']); ?>" required><br>

            <label>Book Name:</label>
            <input type="text" name="book_name" value="<?php echo htmlspecialchars($book['book_name']); ?>" required><br>

            <label>Author:</label>
            <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required><br>

            <label>ISBN:</label>
            <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required><br>

            <label>Year:</label>
            <input type="text" name="year" value="<?php echo htmlspecialchars($book['year']); ?>" required><br>

            <button type="submit">Update</button>
        </form>
    </div>
</body>

</html>