<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$idProduit       = !empty($_POST['idProduit']) ? (int)$_POST['idProduit'] : null;
$nomProduit      = trim($_POST['nomProduit'] ?? '');
$idSousCategorie = $_POST['idSousCategorie'] ?? '';
$typeCategorie   = $_POST['typeCategorie'] ?? 'Simple';
$prix            = $_POST['prix'] ?? 0;
$seuil_alerte    = $_POST['seuil_alerte'] ?? 0;
$image           = $_POST['image'] ?? '';
$quantite        = (int)($_POST['quantite'] ?? 0);
$datePeremption  = !empty($_POST['datePeremption']) ? $_POST['datePeremption'] : null;
$idEvenement     = null;

if ($quantite <= 0) {
    echo json_encode(['success' => false, 'message' => 'La quantité doit être supérieure à 0']);
    exit;
}

try {
    $pdo->beginTransaction();

    if (!$idProduit) {
        if (!$nomProduit || !$idSousCategorie || !$prix) {
            echo json_encode(['success' => false, 'message' => 'Données incomplètes pour le nouveau produit']);
            exit;
        }
        $stmt = $pdo->prepare("
            INSERT INTO Produit (nomProduit, idSousCategorie, typeCategorie, Prix, seuil_alerte, image)
            VALUES (:nom, :cat, :type, :prix, :seuil, :image)
        ");
        $stmt->execute([
            ':nom'   => $nomProduit,
            ':cat'   => $idSousCategorie,
            ':type'  => $typeCategorie,
            ':prix'  => $prix,
            ':seuil' => $seuil_alerte,
            ':image' => $image,
        ]);
        $idProduit = (int)$pdo->lastInsertId();
    }

    $stmtUp = $pdo->prepare("
        UPDATE Stock SET Quantite = Quantite + :qty
        WHERE idProduit = :id AND datePeremption <=> :date
    ");
    $stmtUp->execute([':qty' => $quantite, ':id' => $idProduit, ':date' => $datePeremption]);

    if ($stmtUp->rowCount() === 0) {
        $stmtIns = $pdo->prepare("
            INSERT INTO Stock (idProduit, idEntrepot, idEvenement, Quantite, datePeremption)
            VALUES (:idProduit, :idEntrepot, :idEvenement, :quantite, :datePeremption)
        ");
        $stmtIns->execute([
            ':idProduit'      => $idProduit,
            ':idEntrepot'     => 1,
            ':idEvenement'    => $idEvenement,
            ':quantite'       => $quantite,
            ':datePeremption' => $datePeremption,
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
}
