<?php
header('Content-Type: application/json');
require 'db.php'; 

try {
    $query = $pdo->query("SELECT id, nom, prenom, email, role, telephone FROM Utilisateur");
    $utilisateurs = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($utilisateurs as &$u) {
        $u['statut'] = ($u['role'] === 'EN_ATTENTE') ? 'en_attente' : 'valide';
    }

    echo json_encode($utilisateurs);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>