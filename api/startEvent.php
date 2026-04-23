<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!isset($data['nom']) || !isset($data['idEntrepot'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO evenement (nomEvenement, dateEvenement, statut, idEntrepot) VALUES (:nom, NOW(), 'En cours', :idEntrepot)");
    $stmt->execute([
        ':nom' => $data['nom'],
        ':idEntrepot' => $data['idEntrepot']
    ]);

    $idEvenement = $pdo->lastInsertId();

    $_SESSION['active_event_id'] = $idEvenement;
    $_SESSION['active_entrepot_id'] = $data['idEntrepot'];

    $_SESSION['active_event_nom'] = $data['nom'];
    $_SESSION['active_entrepot_nom'] = $data['nomEntrepot'];

    echo json_encode(['success' => true, 'idEvenement' => $idEvenement]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>