<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();

        // Supprimer les mouvements liés aux stocks du produit
        $pdo->prepare("
            DELETE FROM MouvementStock WHERE idStock IN (
                SELECT idStock FROM Stock WHERE idProduit = :id
            )
        ")->execute(['id' => $_POST['id']]);

        // Supprimer les lignes de commande liées au produit
        $pdo->prepare("DELETE FROM ligne_Commande WHERE idproduit = :id")
            ->execute(['id' => $_POST['id']]);

        // Supprimer les compositions liées au produit
        $pdo->prepare("DELETE FROM Composition WHERE idProduit = :id OR idIngredient = :id")
            ->execute(['id' => $_POST['id']]);

        // Supprimer les stocks du produit
        $pdo->prepare("DELETE FROM Stock WHERE idProduit = :id")
            ->execute(['id' => $_POST['id']]);

        // Supprimer le produit
        $pdo->prepare("DELETE FROM Produit WHERE idproduit = :id")
            ->execute(['id' => $_POST['id']]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}
?>