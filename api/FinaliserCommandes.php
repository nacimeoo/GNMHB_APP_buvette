<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['ids']) || empty($data['ids']) || !isset($data['methode'])) {
    echo json_encode(['success' => false, 'message' => 'Aucune commande sélectionnée.']);
    exit;
}

$ids = $data['ids'];
$methode = $data['methode'];

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "UPDATE Commande 
            SET etatPaiement = 1, modePAIEMENT = ? 
            WHERE idCommande IN ($placeholders) OR numTicket IN ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    
    $params = array_merge([$methode], $ids, $ids);
    $stmt->execute($params);

    echo json_encode([
        'success' => true, 
        'message' => count($ids) . ' commande(s) réglée(s) avec succès en ' . $methode . '.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
}
?>