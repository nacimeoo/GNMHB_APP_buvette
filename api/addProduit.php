<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nomProduit = $_POST['nomProduit'] ?? '';
    $idSousCategorie = $_POST['idSousCategorie'] ?? ''; 
    $typeCategorie = $_POST['typeCategorie'] ?? '';
    $prix = $_POST['prix'] ?? 0;
    $seuil_alerte = $_POST['seuil_alerte'] ?? 0;
    $image = $_POST['image'] ?? '';
    $quantite = $_POST['quantite'] ?? 0; 
    $datePeremption = $_POST['datePeremption'] ?? null;
    
    $idEvenement = null;

    if (!empty($nomProduit) && !empty($idSousCategorie)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Produit (nomProduit, idSousCategorie, typeCategorie, Prix, seuil_alerte, image) VALUES (:nomProduit, :idSousCategorie, :typeCategorie, :prix, :seuil_alerte, :image)");

            $stmt->execute([
                'nomProduit' => $nomProduit,
                'idSousCategorie' => $idSousCategorie,
                'typeCategorie' => $typeCategorie,
                'prix' => $prix,
                'seuil_alerte' => $seuil_alerte,
                'image' => $image
            ]);

            $nouveauProduitId = $pdo->lastInsertId();

            $stmtStock = $pdo->prepare("INSERT INTO Stock (idProduit, idEntrepot, idEvenement, Quantite, datePeremption) VALUES (:idProduit, :idEntrepot, :idEvenement, :quantite, :datePeremption)");

            $stmtStock->execute([
                'idProduit' => $nouveauProduitId,
                'idEntrepot' => 1,
                'idEvenement' => $idEvenement,
                'quantite' => $quantite,
                'datePeremption' => $datePeremption
            ]);

            $pdo->commit();

            echo json_encode(['success' => true]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
}
?>