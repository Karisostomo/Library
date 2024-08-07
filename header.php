<?php
// header.php
?>

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

<!-- User Profile Dropdown -->
<div class="profile-header">
    <div class="profile-info">
        <span class="profile-name"><?php echo htmlspecialchars($user_name); ?></span>
        <div class="dropdown">
            <button class="dropbtn">Profile</button>
            <div class="dropdown-content">
                <a href="update_user.php">Update Profile</a>
                <!-- You can add more options here if needed -->
            </div>
        </div>
    </div>
</div>
