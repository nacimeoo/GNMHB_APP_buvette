<?php
header('Content-Type: application/json');
require_once 'db.php';

$stmtVente = $pdo->query("
    SELECT
        COALESCE(SUM(Montant), 0)          AS totalVente,
        COUNT(*)                            AS nbCommandes,
        COUNT(DISTINCT idUtilisateur)       AS nbClients
    FROM Commande
    WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())
");
$vente = $stmtVente->fetch(PDO::FETCH_ASSOC);

$stmtDepense = $pdo->query("
    SELECT COALESCE(SUM(montantTotal), 0) AS totalDepense
    FROM BonEntree
    WHERE MONTH(dateBon) = MONTH(CURDATE()) AND YEAR(dateBon) = YEAR(CURDATE())
");
$depense = $stmtDepense->fetch(PDO::FETCH_ASSOC);

$totalVente   = round((float)$vente['totalVente'],   2);
$totalDepense = round((float)$depense['totalDepense'], 2);
$totalBenef   = round($totalVente - $totalDepense, 2);

echo json_encode([
    'totalVente'   => $totalVente,
    'totalDepense' => $totalDepense,
    'totalBenef'   => $totalBenef,
    'nbClients'    => (int)$vente['nbClients'],
    'nbCommandes'  => (int)$vente['nbCommandes'],
]);
