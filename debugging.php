<?php
session_start();
require 'config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user name from the database
$stmt = $pdo->prepare("SELECT Name FROM ccai_user WHERE id = :id");
$stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();
$user_name = $user['Name'] ?? 'Guest'; // Default to 'Guest' if no name is found

// Initialize $books as an empty array
$books = [];

// Prepare the search query
$searchQuery = $_GET['search'] ?? '';

// Determine sorting order based on the time since page load
$showNewestFirst = isset($_GET['newest']) && $_GET['newest'] === 'true';
$sortingOrder = $showNewestFirst ? "ORDER BY created_at DESC" : "ORDER BY year DESC";

// Pagination variables
$itemsPerPage = 20;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Fetch total number of books for pagination
try {
    $countSql = "SELECT COUNT(*) FROM books";
    $countParams = [];

    if ($searchQuery) {
        $countSql .= " WHERE book_name LIKE :search OR author LIKE :search";
        $countParams[':search'] = '%' . $searchQuery . '%';
    }

    $countStmt = $pdo->prepare($countSql);
    if ($searchQuery) {
        $countStmt->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalBooks = $countStmt->fetchColumn();
    $totalPages = ceil($totalBooks / $itemsPerPage);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch books data based on search query and pagination
try {
    $sql = "SELECT id, book_category, book_name, author, isbn, year FROM books";
    $searchParams = [];

    if ($searchQuery) {
        $sql .= " WHERE book_name LIKE :search OR author LIKE :search";
        $searchParams[':search'] = '%' . $searchQuery . '%';
    }

    // Add sorting order to the query
    $sql .= " " . $sortingOrder;

    // Add pagination to the query
    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    if ($searchQuery) {
        $stmt->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Check for notification parameter
$notification = $_GET['notification'] ?? '';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Inventory</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/debug.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this book?')) {
                window.location.href = 'delete_inventory.php?id=' + id;
            }
        }

        $(document).ready(function() {
            if ('<?php echo $notification; ?>' === 'deleted') {
                $('#notification').text('Book deleted successfully').fadeIn().delay(3000).fadeOut();
            } else if ('<?php echo $notification; ?>' === 'updated') {
                $('#notification').text('Book updated successfully').fadeIn().delay(3000).fadeOut();
            } else if ('<?php echo $notification; ?>' === 'added') {
                $('#notification').text('Book added successfully').fadeIn().delay(3000).fadeOut();
            }

            // Set timer to switch sorting to default after 1 minute
            setTimeout(function() {
                window.location.href = 'inventory.php';
            }, 60000); // 60000 milliseconds = 1 minute
        });
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

    <div class="dashboard-container">
        <!-- User header section -->
        <div id="notification" class="notification"></div>

        <div class="search-filter-section">
            <form action="inventory.php" method="get">
                <div class="search-filter-container">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table id="books-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books) : ?>
                        <?php foreach ($books as $book) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_category'] ?: 'N/A'); ?></td>
                                <td class="bookName"><?php echo htmlspecialchars($book['book_name']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td class="isb"><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['year']); ?></td>
                                <td class="action-icons">
                                    <a href="#" class="icon update" data-id="<?php echo $book['id']; ?>" title="Update"><i class="fas fa-edit"></i></a>
                                    <div class="icon delete" title="Delete" onclick="confirmDelete(<?php echo $book['id']; ?>)"><i class="fas fa-trash"></i></div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">No records found</td>
                        </tr>
                    <?php endif; ?>
                    <!-- Edit Modal -->
                    <div id="editModal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Edit Book</h2>
                            <form id="editForm" method="POST" action="update_inventory.php">
                                <input class="input" type="hidden" name="id" id="editBookId">
                                <label>Category:</label>
                                <input class="input" type="text" name="book_category" id="editBookCategory" required><br>
                                <label>Book Name:</label>
                                <input class="input" type="text" name="book_name" id="editBookName" required><br>
                                <label>Author:</label>
                                <input class="input" type="text" name="author" id="editAuthor" required><br>
                                <label>ISBN:</label>
                                <input class="input" type="text" name="isbn" id="editISBN" required><br>
                                <label>Year:</label>
                                <input class="input" type="text" name="year" id="editYear" required><br>
                                <button class="updateButton" type="submit">Update</button>
                            </form>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {
                            var modal = document.getElementById("editModal");
                            var span = document.getElementsByClassName("close")[0];

                            $('.update').on('click', function(event) {
                                event.preventDefault();
                                var bookId = $(this).data('id');

                                $.ajax({
                                    url: 'fetch_book.php',
                                    type: 'GET',
                                    data: {
                                        id: bookId
                                    },
                                    success: function(data) {
                                        var book = JSON.parse(data);
                                        $('#editBookId').val(book.id);
                                        $('#editBookCategory').val(book.book_category);
                                        $('#editBookName').val(book.book_name);
                                        $('#editAuthor').val(book.author);
                                        $('#editISBN').val(book.isbn);
                                        $('#editYear').val(book.year);
                                        modal.style.display = "block";
                                    }
                                });
                            });

                            span.onclick = function() {
                                modal.style.display = "none";
                            }

                            window.onclick = function(event) {
                                if (event.target == modal) {
                                    modal.style.display = "none";
                                }
                            }
                        });
                    </script>

                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($totalPages > 1) : ?>
                <!-- First Page Link -->
                <?php if ($currentPage > 1) : ?>
                    <a href="inventory.php?page=1&search=<?php echo urlencode($searchQuery); ?>&newest=<?php echo $showNewestFirst ? 'true' : 'false'; ?>" class="pagination-link">First</a>
                <?php endif; ?>

                <!-- Previous Page Link -->
                <?php if ($currentPage > 1) : ?>
                    <a href="inventory.php?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($searchQuery); ?>&newest=<?php echo $showNewestFirst ? 'true' : 'false'; ?>" class="pagination-link">&laquo; Previous</a>
                <?php endif; ?>

                <!-- Page Number Links -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1) {
                    echo '<a href="inventory.php?page=1&search=' . urlencode($searchQuery) . '&newest=' . ($showNewestFirst ? 'true' : 'false') . '" class="pagination-link">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="pagination-ellipsis">...</span>';
                    }
                }

                for ($page = $startPage; $page <= $endPage; $page++) :
                    $activeClass = $page == $currentPage ? 'active' : '';
                ?>
                    <a href="inventory.php?page=<?php echo $page; ?>&search=<?php echo urlencode($searchQuery); ?>&newest=<?php echo $showNewestFirst ? 'true' : 'false'; ?>" class="pagination-link <?php echo $activeClass; ?>"><?php echo $page; ?></a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="pagination-ellipsis">...</span>';
                    }
                    echo '<a href="inventory.php?page=' . $totalPages . '&search=' . urlencode($searchQuery) . '&newest=' . ($showNewestFirst ? 'true' : 'false') . '" class="pagination-link">' . $totalPages . '</a>';
                } ?>

                <!-- Next Page Link -->
                <?php if ($currentPage < $totalPages) : ?>
                    <a href="inventory.php?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($searchQuery); ?>&newest=<?php echo $showNewestFirst ? 'true' : 'false'; ?>" class="pagination-link">Next &raquo;</a>
                <?php endif; ?>

                <!-- Last Page Link -->
                <?php if ($currentPage < $totalPages) : ?>
                    <a href="inventory.php?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($searchQuery); ?>&newest=<?php echo $showNewestFirst ? 'true' : 'false'; ?>" class="pagination-link">Last</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>