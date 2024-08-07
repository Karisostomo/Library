<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM ccai_user");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle profile update and deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $f_name = $_POST['f_name'];
        $l_name = $_POST['l_name'];
        $username = $_POST['username'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        $updateQuery = "UPDATE ccai_user SET F_Name = ?, L_Name = ?, Username = ?";
        $params = [$f_name, $l_name, $username];

        if ($password) {
            $updateQuery .= ", Password = ?";
            $params[] = $password;
        }

        $updateQuery .= " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute($params);

        $message = "<p>User updated successfully.</p>";
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM ccai_user WHERE id = ?");
        $stmt->execute([$user_id]);

        $message = "<p>User deleted successfully.</p>";
    }
    // Refresh the users after update or deletion
    $stmt = $pdo->query("SELECT * FROM ccai_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Settings</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/settings.css">
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
        <div class="settings-table-container">
            <h2>User Settings</h2>
            <?php if (isset($message)) echo $message; ?>
            <table class="settings-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['F_Name']); ?></td>
                            <td><?php echo htmlspecialchars($user['L_Name']); ?></td>
                            <td><?php echo htmlspecialchars($user['Username']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="f_name" value="<?php echo htmlspecialchars($user['F_Name']); ?>">
                                    <input type="hidden" name="l_name" value="<?php echo htmlspecialchars($user['L_Name']); ?>">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>">
                                    <button type="submit" name="update_user">Update</button>
                                    <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
