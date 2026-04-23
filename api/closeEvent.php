<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (isset($_SESSION['active_event_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE evenement SET statut = 'Terminé' WHERE idEvenement = :id");
        $stmt->execute([':id' => $_SESSION['active_event_id']]);
        
        unset($_SESSION['active_event_id']);
        unset($_SESSION['active_entrepot_id']);
        unset($_SESSION['active_event_nom']);    
        unset($_SESSION['active_entrepot_nom']);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => true]); 
}
?>