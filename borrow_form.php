<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $b_class = $_POST['b_class'];
    $b_bookname = $_POST['b_bookname'];

    // Check for 'Others' option
    if (isset($_POST['b_class_other']) && $_POST['b_class'] === 'Others') {
        $b_class = $_POST['b_class_other'];
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO borrowers (name, number, b_class, b_bookname, borrow_status) VALUES (?, ?, ?, ?, 'borrowed')");
    $stmt->execute([$name, $number, $b_class, $b_bookname]);

    $_SESSION['message'] = 'Book borrowed successfully.';
    header('Location: borrow.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrow Form</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/borrow_form.css">
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
        <div class="borrow-form">
            <h2>Borrow Form</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="number">ID Number:</label>
                    <input type="text" id="number" name="number" required>
                </div>

                <div class="form-group">
                    <label>Type of Borrower:</label>
                    <div class="borrow-type">
                        <label>
                            <input type="radio" id="teacher" name="b_class" value="Teacher" required>
                            Teacher
                        </label>
                        <label>
                            <input type="radio" id="student" name="b_class" value="Student" required>
                            Student
                        </label>
                        <label>
                            <input type="radio" id="others" name="b_class" value="Others" required>
                            Others
                        </label>
                        <input type="text" id="other_class" name="b_class_other" placeholder="Specify" class="specify-input">
                    </div>
                </div>

                <div class="form-group">
                    <label for="b_bookname">Book Name:</label>
                    <input type="text" id="b_bookname" name="b_bookname" required>
                    <div id="bookname-suggestions" class="autocomplete-suggestions"></div>
                </div>

                <button type="submit" class="submit-button">Submit</button>
            </form>
        </div>
    </div>

    <script>
    // Function to show or hide "Specify" input based on selected borrower type
    function updateSpecifyInput() {
        const specifyInput = document.getElementById('other_class');
        const selectedValue = document.querySelector('input[name="b_class"]:checked')?.value;
        
        if (selectedValue === 'Others') {
            specifyInput.classList.add('active');
            specifyInput.focus();
        } else {
            specifyInput.classList.remove('active');
        }
    }

    // Attach event listeners to radio buttons
    document.querySelectorAll('input[name="b_class"]').forEach((radio) => {
        radio.addEventListener('change', updateSpecifyInput);
    });

    // Initialize display state of "Specify" input on page load
    updateSpecifyInput();

    // Autocomplete for book names
    document.getElementById('b_bookname').addEventListener('input', function() {
        const query = this.value;
        if (query.length >= 2) {
            fetch(`search_books.php?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    const suggestions = document.getElementById('bookname-suggestions');
                    suggestions.innerHTML = '';
                    data.forEach(book => {
                        const div = document.createElement('div');
                        div.classList.add('autocomplete-suggestion');
                        div.textContent = book.book_name;
                        div.addEventListener('click', function() {
                            document.getElementById('b_bookname').value = book.book_name;
                            suggestions.innerHTML = ''; // Clear suggestions
                        });
                        suggestions.appendChild(div);
                    });
                });
        } else {
            document.getElementById('bookname-suggestions').innerHTML = ''; // Clear suggestions if input length < 2
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!document.getElementById('b_bookname').contains(e.target) && !document.getElementById('bookname-suggestions').contains(e.target)) {
            document.getElementById('bookname-suggestions').innerHTML = '';
        }
    });
</script>

</body>
</html>
