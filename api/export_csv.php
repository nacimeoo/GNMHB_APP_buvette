<?php
$type = isset($_GET['type']) ? $_GET['type'] : '';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="export_' . $type . '_' . date('Ymd') . '.csv"');

$output = fopen('php://output', 'w');

fputs($output, "\xEF\xBB\xBF");

switch ($type) {
    case 'finance':
        fputcsv($output, ['Mois', 'Ventes (EUR)', 'Dépenses (EUR)', 'Bénéfices (EUR)'], ';');

        $mois = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];
        $ventes = [15000, 18000, 16500, 22000, 25000, 28000, 26000, 21000, 24000, 29000, 32000, 38000];
        $depenses = [10000, 11000, 10500, 13000, 14000, 15000, 14500, 12000, 13500, 16000, 18000, 20000];

        for ($i = 0; $i < count($mois); $i++) {
            $benefice = $ventes[$i] - $depenses[$i];
            
            $ligne = [
                $mois[$i], 
                $ventes[$i], 
                $depenses[$i], 
                $benefice
            ];
            
            fputcsv($output, $ligne, ';');
        }
        break;

    case 'stock':
        fputcsv($output, ['Produit', 'Quantité en stock'], ';');

        $produitsStock = ['Coca-cola 50cl', 'Coca-cola Zero 50cl', 'Oasis 50cl', 'Fuze Tea 50cl', 'KitKat'];
        $quantitesActuelles = [120, 300, 85, 450, 200];

        for ($i = 0; $i < count($produitsStock); $i++) {
            fputcsv($output, [$produitsStock[$i], $quantitesActuelles[$i]], ';');
        }
        break;
}

fclose($output);
exit;
?>