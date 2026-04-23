<?php
require_once 'db.php';

$year = date('Y');

$mois    = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];

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


if (ob_get_length()) ob_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="rapport_financier_' . $year . '.csv"');
header('Cache-Control: max-age=0');

$output = fopen('php://output', 'w');

fputs($output, "\xEF\xBB\xBF");

$delimiter = ';';

fputcsv($output, ['Mois', 'Vente (EUR)', 'Dépense (EUR)', 'Bénéfice (EUR)'], $delimiter);

foreach ($mois as $i => $m) {
    $row = [
        $m,
        number_format($vente[$i],   2, ',', ''),
        number_format($depense[$i], 2, ',', ''),
        number_format($benef[$i],   2, ',', '')
    ];
    fputcsv($output, $row, $delimiter);
}

$rowTotal = [
    'TOTAL',
    number_format(array_sum($vente),   2, ',', ''),
    number_format(array_sum($depense), 2, ',', ''),
    number_format(array_sum($benef),   2, ',', '')
];
fputcsv($output, $rowTotal, $delimiter);

fclose($output);
exit;