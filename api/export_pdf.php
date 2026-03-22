<?php
require(dirname(__FILE__) . '/fpdf/fpdf.php');

$type = $_GET['type'];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

switch ($type) {
    case 'finance':
        $mois = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];
        $ventes = [15000, 18000, 16500, 22000, 25000, 28000, 26000, 21000, 24000, 29000, 32000, 38000];
        $depenses = [10000, 11000, 10500, 13000, 14000, 15000, 14500, 12000, 13500, 16000, 18000, 20000];

        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Rapport d\'Activité Annuel'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(26, 45, 79);
        $pdf->SetTextColor(255, 255, 255);

        $width = 45;
        $pdf->Cell(20, 10, 'Mois', 1, 0, 'C', true);
        $pdf->Cell($width, 10, 'Ventes', 1, 0, 'C', true);
        $pdf->Cell($width, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Dépenses'), 1, 0, 'C', true);
        $pdf->Cell($width, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Bénéfices'), 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);

        $fill = false;
        for ($i = 0; $i < count($mois); $i++) {
            $pdf->SetFillColor(245, 245, 245);
            $benefice = $ventes[$i] - $depenses[$i];

            $pdf->Cell(20, 8, $mois[$i], 1, 0, 'C', $fill);
            $pdf->Cell($width, 8, number_format($ventes[$i], 0, '.', ' ') . ' EUR', 1, 0, 'R', $fill);
            $pdf->Cell($width, 8, number_format($depenses[$i], 0, '.', ' ') . ' EUR', 1, 0, 'R', $fill);

            $pdf->SetTextColor(184, 134, 11);
            $pdf->Cell($width, 8, number_format($benefice, 0, '.', ' ') . ' EUR', 1, 1, 'R', $fill);
            $pdf->SetTextColor(0, 0, 0);

            $fill = !$fill;
        }

        $pdf->Output('I', 'rapport_statistique.pdf');
        break;
    case 'stock':
        $produitsStock = ['Coca-cola 50cl', 'Coca-cola Zero 50cl', 'Oasis 50cl', 'Fuze Tea 50cl', 'Vittel 50cl', 'S.Pellegrino 50cl', 'KitKat', 'Twix', 'Snickers', 'mars'];
        $quantitesActuelles = [120, 300, 85, 450, 42, 150, 200, 180, 160, 90];

        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Stock Actuel par Produit'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(26, 45, 79);
        $pdf->SetTextColor(255, 255, 255);

        $width = 45;
        $pdf->Cell(100, 10, 'Produit', 1, 0, 'C', true);
        $pdf->Cell($width, 10, 'Quantite', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);

        $fill = false;
        for ($i = 0; $i < count($produitsStock); $i++) {
            $pdf->SetFillColor(245, 245, 245);

            $pdf->Cell(100, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $produitsStock[$i]), 1, 0, 'L', $fill);
            $pdf->Cell($width, 8, number_format($quantitesActuelles[$i], 0, '.', ' '), 1, 1, 'R', $fill);
            $fill = !$fill;
        }

        $pdf->Output('I', 'rapport_stock.pdf');
        break;

        // gener un pdf de bon commande avec les produits et les quantites a commander et le total a la fin 
        case 'commande' :
            $produitsCommande = ['Coca-cola 50cl', 'Coca-cola Zero 50cl'];
            $prixCommande = [1.50, 1.50];
            $quantitesCommande = [120, 300];
            $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Bon de Commande'), 0, 1, 'C');
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(26, 45, 79);
            $pdf->SetTextColor(255, 255, 255);
            $colProduit = 90;
            $colPrix = 45;
            $colQte = 45;
            $pdf->Cell($colProduit, 10, 'Produit', 1, 0, 'C', true);
            $pdf->Cell($colPrix, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Prix unitaire'), 1, 0, 'C', true);
            $pdf->Cell($colQte, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Quantité'), 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 10);
            $fill = false;
            $total = 0;
            for ($i = 0; $i < count($produitsCommande); $i++) {
                $pdf->SetFillColor(245, 245, 245);
                $pdf->Cell($colProduit, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $produitsCommande[$i]), 1, 0, 'L', $fill);
                $pdf->Cell($colPrix, 8, number_format($prixCommande[$i], 2, '.', ' ') . ' EUR', 1, 0, 'R', $fill);
                $pdf->Cell($colQte, 8, number_format($quantitesCommande[$i], 0, '.', ' '), 1, 1, 'R', $fill);
                $total += $quantitesCommande[$i] * $prixCommande[$i];
                $fill = !$fill;
            }
            $pdf->Cell($colProduit + $colPrix, 8, 'Total', 1, 0, 'L', $fill);
            $pdf->Cell($colQte, 8, number_format($total, 2, '.', ' ') . ' EUR', 1, 1, 'R', $fill);
            $pdf->Output('I', 'bon_commande.pdf');
            break;

    default:
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Rapport d\'Activité Annuel'), 0, 1, 'C');
}

