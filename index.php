<?php
session_start();
require 'config.php'; // Ensure this line is uncommented to include your database connection configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $Password = $_POST['Password']; // Updated to match input field name

    // Prepare and execute the query
    $stmt = $pdo->prepare('SELECT * FROM ccai_user WHERE Username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Debugging: Output fetched user data
    error_log(print_r($user, true));

    if ($user) {
        // Check if the password matches
        if (password_verify($Password, $user['Password'])) { // Use 'Password' from database
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_label'] = $user['Label']; // Store user label in session
            header('Location: home.php');
            exit;
        } else {
            $error = 'Incorrect password';
        }
    } else {
        $error = 'Username does not exist';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            <div class="input-group password-wrapper">
                <input type="password" name="Password" placeholder="Password" required>
                <i class="fas fa-lock input-icon"></i>
                <i class="fas fa-eye toggle-password"></i>
            </div>

            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) : ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <a href="register.php" class="register-link">Register</a>
    </div>
    <script src="JavaScript/password-toggle.js"></script>
     <!-- Include your JavaScript for toggling password visibility -->
</body>

</html>
