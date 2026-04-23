<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();

        $pdo->prepare("
            DELETE FROM MouvementStock WHERE idStock IN (
                SELECT idStock FROM Stock WHERE idProduit = :id
            )
        ")->execute(['id' => $_POST['id']]);

        $pdo->prepare("DELETE FROM ligne_Commande WHERE idproduit = :id")
            ->execute(['id' => $_POST['id']]);

        $pdo->prepare("DELETE FROM Composition WHERE idProduit = :id OR idIngredient = :id")
            ->execute(['id' => $_POST['id']]);

        $pdo->prepare("DELETE FROM Stock WHERE idProduit = :id")
            ->execute(['id' => $_POST['id']]);

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