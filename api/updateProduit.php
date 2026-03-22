<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idproduit'])) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE Produit SET nomProduit = :nomProduit, idSousCategorie = :idSousCategorie, typeCategorie = :typeCategorie, Prix = :prix, seuil_alerte = :seuil_alerte, image = :image WHERE idproduit = :idproduit");

        $stmt->execute([
            'idproduit' => $_POST['idproduit'],
            'nomProduit' => $_POST['nomProduit'],
            'idSousCategorie' => $_POST['idSousCategorie'],
            'typeCategorie' => $_POST['typeCategorie'] ?? 'Simple',
            'prix' => $_POST['prix'],
            'seuil_alerte' => $_POST['seuil_alerte'] ?? 10,
            'image' => $_POST['image']
        ]);

        if (isset($_POST['quantite'])) {
            $stmtStock = $pdo->prepare("UPDATE Stock SET Quantite = :quantite WHERE idProduit = :idproduit");
            $stmtStock->execute([
                'quantite'  => $_POST['quantite'],
                'idproduit' => $_POST['idproduit'],
            ]);
        }

        $pdo->commit();

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données invalides ou ID manquant.']);
}
?>