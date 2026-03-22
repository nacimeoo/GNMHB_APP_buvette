<?php
header('Content-Type: application/json');
require_once 'db.php';

$year = date('Y');

$stmt = $pdo->prepare("
    SELECT
        MONTH(date) AS mois,
        COALESCE(SUM(CASE WHEN modePAIEMENT != 'Credit' THEN montant ELSE 0 END), 0) AS encaisse,
        COALESCE(SUM(CASE WHEN modePAIEMENT = 'Credit' THEN montant ELSE 0 END), 0) AS impayes,
        COALESCE(SUM(montant), 0) AS total
    FROM Commande
    WHERE YEAR(date) = :year
    GROUP BY MONTH(date)
    ORDER BY MONTH(date)
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$encaisse = array_fill(0, 12, 0);
$impayes  = array_fill(0, 12, 0);
$total    = array_fill(0, 12, 0);

foreach ($rows as $row) {
    $idx = (int)$row['mois'] - 1;
    $encaisse[$idx] = round((float)$row['encaisse'], 2);
    $impayes[$idx]  = round((float)$row['impayes'],  2);
    $total[$idx]    = round((float)$row['total'],    2);
}

echo json_encode([
    'encaisse' => $encaisse,
    'impayes'  => $impayes,
    'total'    => $total
]);
