<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$idProduit     = (int)($data['idProduit']  ?? 0);
$idEntrepot    = (int)($data['idEntrepot'] ?? 0);
$quantite      = (int)($data['quantite']   ?? 0);
$idUtilisateur = $_SESSION['user_id']      ?? null;

if (!$idProduit || !$idEntrepot || $quantite <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}
if (!$idUtilisateur) {
    echo json_encode(['success' => false, 'message' => 'Session expirée, veuillez vous reconnecter.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Trouver la ligne de stock (première dont la date de péremption est la plus proche)
    $stmtS = $pdo->prepare("
        SELECT idStock, Quantite FROM Stock
        WHERE idProduit = :idProd AND idEntrepot = :idEnt
        ORDER BY datePeremption ASC
        LIMIT 1
    ");
    $stmtS->execute([':idProd' => $idProduit, ':idEnt' => $idEntrepot]);
    $stock = $stmtS->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        throw new Exception('Aucun stock trouvé pour ce produit dans cet entrepôt.');
    }

    // Décrémenter le stock (minimum 0)
    $pdo->prepare("UPDATE Stock SET Quantite = GREATEST(0, Quantite - :qty) WHERE idStock = :idStock")
        ->execute([':qty' => $quantite, ':idStock' => $stock['idStock']]);

    // Mouvement PERTE
    $pdo->prepare("
        INSERT INTO MouvementStock (idStock, idUtilisateur, typeMouvement, quantite, motif)
        VALUES (:idStock, :idUsr, 'PERTE', :qty, 'Perte déclarée')
    ")->execute([':idStock' => $stock['idStock'], ':idUsr' => $idUtilisateur, ':qty' => $quantite]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
