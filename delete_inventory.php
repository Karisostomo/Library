<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? '';

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    
    header('Location: inventory.php?notification=deleted');
    exit;
}
?>
