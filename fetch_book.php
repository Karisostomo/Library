<?php
require 'config.php';

if (isset($_GET['id'])) {
    $bookId = $_GET['id'];
    $query = "SELECT * FROM books WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id', $bookId, PDO::PARAM_INT);
    $stmt->execute();
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($book);
}

