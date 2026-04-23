<?php
header('Content-Type: application/json');
require_once 'db.php';

$year = date('Y');

$stmtV = $pdo->prepare("
    SELECT MONTH(date) AS mois, COALESCE(SUM(Montant), 0) AS total
    FROM Commande
    WHERE YEAR(date) = :year
    GROUP BY MONTH(date)
");
$stmtV->execute([':year' => $year]);
$rowsVente = $stmtV->fetchAll(PDO::FETCH_ASSOC);

$stmtD = $pdo->prepare("
    SELECT MONTH(dateBon) AS mois, COALESCE(SUM(montantTotal), 0) AS total
    FROM BonEntree
    WHERE YEAR(dateBon) = :year
    GROUP BY MONTH(dateBon)
");
$stmtD->execute([':year' => $year]);
$rowsDepense = $stmtD->fetchAll(PDO::FETCH_ASSOC);

$vente   = array_fill(0, 12, 0);
$depense = array_fill(0, 12, 0);
$benef   = array_fill(0, 12, 0);

foreach ($rowsVente as $row) {
    $vente[(int)$row['mois'] - 1] = round((float)$row['total'], 2);
}
foreach ($rowsDepense as $row) {
    $depense[(int)$row['mois'] - 1] = round((float)$row['total'], 2);
}
for ($i = 0; $i < 12; $i++) {
    $benef[$i] = round($vente[$i] - $depense[$i], 2);
}

echo json_encode([
    'vente'   => $vente,
    'depense' => $depense,
    'benef'   => $benef,
]);
