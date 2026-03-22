<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$idEntrepot    = (int)($data['idEntrepot']  ?? 0);
$date          = $data['date']              ?? date('Y-m-d');
$montantTotal  = isset($data['montantTotal']) && $data['montantTotal'] !== '' ? (float)$data['montantTotal'] : null;
$lignes        = $data['lignes']            ?? [];
$idUtilisateur = $_SESSION['user_id']       ?? null;

if (!$idEntrepot) {
    echo json_encode(['success' => false, 'message' => 'Entrepôt obligatoire.']);
    exit;
}
if (!$idUtilisateur) {
    echo json_encode(['success' => false, 'message' => 'Session expirée, veuillez vous reconnecter.']);
    exit;
}
if (empty($lignes)) {
    echo json_encode(['success' => false, 'message' => 'Aucune ligne produit à enregistrer.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Créer le BonEntree
    $stmtBon = $pdo->prepare("
        INSERT INTO BonEntree (idEntrepot, idUtilisateur, dateBon, montantTotal, motif)
        VALUES (:ent, :usr, :date, :montant, 'Entrée de stock')
    ");
    $stmtBon->execute([
        ':ent'     => $idEntrepot,
        ':usr'     => $idUtilisateur,
        ':date'    => $date . ' 00:00:00',
        ':montant' => $montantTotal,
    ]);
    $idBon = (int)$pdo->lastInsertId();

    foreach ($lignes as $ligne) {
        $idProduit       = !empty($ligne['idProduit'])       ? (int)$ligne['idProduit']       : null;
        $nomProduit      = trim($ligne['nomProduit']         ?? '');
        $quantite        = (int)($ligne['quantite']          ?? 0);
        $datePeremption  = !empty($ligne['datePeremption'])  ? $ligne['datePeremption']        : null;
        $idSousCategorie = !empty($ligne['idSousCategorie']) ? (int)$ligne['idSousCategorie']  : null;
        $prixVente       = !empty($ligne['prix'])            ? (float)$ligne['prix']           : null;

        if ($quantite <= 0) {
            throw new Exception('Quantité invalide pour "' . $nomProduit . '".');
        }

        // Nouveau produit → insérer dans Produit
        if (!$idProduit) {
            if (!$nomProduit || !$idSousCategorie || !$prixVente) {
                throw new Exception('Données incomplètes pour le nouveau produit "' . $nomProduit . '".');
            }
            $stmtP = $pdo->prepare("
                INSERT INTO Produit (nomProduit, idSousCategorie, typeCategorie, Prix, seuil_alerte, Image)
                VALUES (:nom, :cat, 'Simple', :prix, 0, '')
            ");
            $stmtP->execute([':nom' => $nomProduit, ':cat' => $idSousCategorie, ':prix' => $prixVente]);
            $idProduit = (int)$pdo->lastInsertId();
        }

        // Tenter d'incrémenter le stock existant (même produit + entrepôt + date péremption)
        $stmtUp = $pdo->prepare("
            UPDATE Stock SET Quantite = Quantite + :qty
            WHERE idProduit = :idProd AND idEntrepot = :idEnt AND datePeremption <=> :datePer
        ");
        $stmtUp->execute([
            ':qty'     => $quantite,
            ':idProd'  => $idProduit,
            ':idEnt'   => $idEntrepot,
            ':datePer' => $datePeremption,
        ]);

        if ($stmtUp->rowCount() === 0) {
            // Nouvelle ligne de stock
            $stmtIns = $pdo->prepare("
                INSERT INTO Stock (idProduit, idEntrepot, idEvenement, Quantite, datePeremption)
                VALUES (:idProd, :idEnt, NULL, :qty, :datePer)
            ");
            $stmtIns->execute([
                ':idProd'  => $idProduit,
                ':idEnt'   => $idEntrepot,
                ':qty'     => $quantite,
                ':datePer' => $datePeremption,
            ]);
            $idStock = (int)$pdo->lastInsertId();
        } else {
            $stmtS = $pdo->prepare("
                SELECT idStock FROM Stock
                WHERE idProduit = :idProd AND idEntrepot = :idEnt AND datePeremption <=> :datePer
                LIMIT 1
            ");
            $stmtS->execute([':idProd' => $idProduit, ':idEnt' => $idEntrepot, ':datePer' => $datePeremption]);
            $idStock = (int)$stmtS->fetchColumn();
        }

        // Mouvement ENTREE
        $stmtM = $pdo->prepare("
            INSERT INTO MouvementStock (idStock, idUtilisateur, typeMouvement, quantite, motif, idBon)
            VALUES (:idStock, :idUsr, 'ENTREE', :qty, 'Entrée de stock', :idBon)
        ");
        $stmtM->execute([
            ':idStock' => $idStock,
            ':idUsr'   => $idUtilisateur,
            ':qty'     => $quantite,
            ':idBon'   => $idBon,
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
