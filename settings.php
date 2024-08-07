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

// Fetch user information from the database
$stmt = $pdo->prepare("SELECT F_Name, L_Name, Username FROM ccai_user WHERE id = :id");
$stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize variables with default values
$firstName = isset($user['F_Name']) ? htmlspecialchars($user['F_Name']) : 'N/A';
$lastName = isset($user['L_Name']) ? htmlspecialchars($user['L_Name']) : 'N/A';
$username = isset($user['Username']) ? htmlspecialchars($user['Username']) : 'N/A';

// Fetch all users from the database
$users = [];
try {
    $stmt = $pdo->query("SELECT id, F_Name, L_Name, Username FROM ccai_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Check for notification parameter
$notification = $_GET['notification'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Settings</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = 'delete_user.php?id=' + id;
            }
        }

        $(document).ready(function() {
            // Display notifications
            if ('<?php echo $notification; ?>' === 'updated') {
                $('#notification').text('User updated successfully').fadeIn().delay(3000).fadeOut();
            } else if ('<?php echo $notification; ?>' === 'deleted') {
                $('#notification').text('User deleted successfully').fadeIn().delay(3000).fadeOut();
            }

            // Set timer to switch page or refresh
            setTimeout(function() {
                window.location.href = 'settings.php';
            }, 60000); // 60000 milliseconds = 1 minute

            // Live search for users
            $('#searchUser').on('keyup', function() {
                var query = $(this).val();
                $.ajax({
                    url: 'search_users.php',
                    type: 'GET',
                    data: { query: query },
                    success: function(data) {
                        $('tbody').html(data);
                    }
                });
            });

            // Modal for updating user
            var modal = document.getElementById("updateUserModal");
            var span = document.getElementsByClassName("close")[0];

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            $('.update').click(function(e) {
                e.preventDefault();
                var userId = $(this).data('id');
                $.ajax({
                    url: 'get_user.php', // Script to fetch user data
                    type: 'GET',
                    data: { id: userId },
                    success: function(response) {
                        var user = JSON.parse(response);
                        $('#updateUserId').val(user.id);
                        $('#updateFirstName').val(user.F_Name);
                        $('#updateLastName').val(user.L_Name);
                        $('#updateUsername').val(user.Username);
                        modal.style.display = "block";
                    }
                });
            });
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
        <div id="notification" class="notification"></div>

        <div class="search-container">
            <form action="settings.php" method="get">
                <input type="text" name="query" id="searchUser" placeholder="Search Users.." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
                <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <h2>Search Users</h2>

        <div class="settings-table-container">
            <table id="users-table" class="settings-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users) : ?>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['F_Name']); ?></td>
                                <td><?php echo htmlspecialchars($user['L_Name']); ?></td>
                                <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                <td class="action-icons">
                                    <a href="#" class="icon update" data-id="<?php echo $user['id']; ?>" title="Update"><i class="fas fa-edit"></i></a>
                                    <div class="icon delete" title="Delete" onclick="confirmDelete(<?php echo $user['id']; ?>)"><i class="fas fa-trash"></i></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="create-account-container">
            <a href="register.php" class="create-account-button">Create an Account</a>
        </div>
    </div>

    <!-- Update User Modal -->
    <div id="updateUserModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form id="updateUserForm" action="update_user.php" method="post">
                <input type="hidden" name="id" id="updateUserId">
                <div class="form-group">
                    <label for="updateFirstName">First Name</label>
                    <input type="text" id="updateFirstName" name="F_Name" required>
                </div>
                <div class="form-group">
                    <label for="updateLastName">Last Name</label>
                    <input type="text" id="updateLastName" name="L_Name" required>
                </div>
                <div class="form-group">
                    <label for="updateUsername">Username</label>
                    <input type="text" id="updateUsername" name="Username" required>
                </div>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

</body>
</html>
