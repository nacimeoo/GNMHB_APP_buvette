<?php
require 'db.php';
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE Utilisateur SET role = 'BENEVOLE' WHERE id = :id");
    $stmt->execute(['id' => $_POST['id']]);
    echo json_encode(['success' => true]);
}
?>