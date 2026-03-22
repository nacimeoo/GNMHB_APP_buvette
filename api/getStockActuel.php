<?php
header('Content-Type: application/json');
require_once 'db.php';

$stmt = $pdo->query("
    SELECT p.nomProduit, COALESCE(SUM(s.Quantite), 0) AS quantiteStock
    FROM Produit p
    LEFT JOIN Stock s ON s.idProduit = p.idproduit
    WHERE p.typeCategorie != 'Matiere_Premiere'
    GROUP BY p.idproduit, p.nomProduit
    HAVING quantiteStock > 0
    ORDER BY quantiteStock DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$noms     = [];
$quantites = [];
foreach ($rows as $row) {
    $noms[]      = $row['nomProduit'];
    $quantites[] = (int)$row['quantiteStock'];
}

echo json_encode(['noms' => $noms, 'quantites' => $quantites]);
