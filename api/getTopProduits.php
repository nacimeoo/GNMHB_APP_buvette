<?php
header('Content-Type: application/json');
require_once 'db.php';

$stmt = $pdo->query("
    SELECT p.nomProduit, SUM(lc.quantite) AS totalVendu
    FROM ligne_commande lc
    INNER JOIN Produit p ON lc.idproduit = p.idproduit
    GROUP BY lc.idproduit, p.nomProduit
    ORDER BY totalVendu DESC
    LIMIT 3
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$noms     = [];
$quantites = [];
foreach ($rows as $row) {
    $noms[]      = $row['nomProduit'];
    $quantites[] = (int)$row['totalVendu'];
}

echo json_encode(['noms' => $noms, 'quantites' => $quantites]);
