<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$idCommande = intval($input['idCommande'] ?? 0);

if (!$idCommande) {
    echo json_encode(['success' => false, 'message' => 'ID commande manquant.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE Commande SET etatPreparation = 1 WHERE idCommande = :id");
$stmt->execute([':id' => $idCommande]);

echo json_encode(['success' => true]);
?>
