<?php
require('fpdf/fpdf.php');
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


define('BLEU_R', 26);  define('BLEU_G', 31);  define('BLEU_B', 94);   // #1a1f5e
define('OR_R',  180);  define('OR_G',  150);  define('OR_B',  60);    // #b4963c
define('GRIS_R',245);  define('GRIS_G',245);  define('GRIS_B',250);   // fond lignes paires

$pdf = new FPDF();
$pdf->AddPage('L'); // Paysage
$pdf->SetMargins(15, 15, 15);

// ── HEADER ─────────────────────────────────────────────────────────────────
// Bandeau bleu marine en haut
$pdf->SetFillColor(BLEU_R, BLEU_G, BLEU_B);
$pdf->Rect(0, 0, 300, 38, 'F');

// Logo (adapte le chemin)
$logoPath = '../images/logoClub.png'; // ← ton chemin vers le logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 8, 4, 24); // x, y, largeur
}

// Titre principal
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(42, 8);
$pdf->Cell(0, 8, 'NANCY HANDBALL', 0, 1, 'L');

// Sous-titre
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(OR_R, OR_G, OR_B);
$pdf->SetXY(43, 18);
$pdf->Cell(0, 6, 'Rapport Financier Annuel - ' . date('Y'), 0, 1, 'L');

// Date de génération (droite)
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(200, 200, 220);
$pdf->SetXY(0, 26);
$pdf->Cell(282, 6, 'Genere le ' . date('d/m/Y a H:i'), 0, 1, 'R');

$pdf->Ln(8);

// ── LIGNE DÉCORATIVE OR ────────────────────────────────────────────────────
$pdf->SetDrawColor(OR_R, OR_G, OR_B);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, $pdf->GetY(), 282, $pdf->GetY());
$pdf->Ln(4);

// ── CALCUL POUR LE CENTRAGE ────────────────────────────────────────────────
$col = [32, 62, 62, 62];
$tableWidth = array_sum($col); // 218
$pageWidth = $pdf->GetPageWidth(); // 297 en A4 Paysage
$startX = ($pageWidth - $tableWidth) / 2; // Position de départ pour centrer

// ── EN-TÊTES TABLEAU ───────────────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(BLEU_R, BLEU_G, BLEU_B);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(BLEU_R, BLEU_G, BLEU_B);

$pdf->SetX($startX); // On se place au centre
$pdf->Cell($col[0], 9, 'MOIS',       1, 0, 'C', true);
$pdf->Cell($col[1], 9, 'VENTE',      1, 0, 'C', true);
$pdf->Cell($col[2], 9, utf8_decode('DÉPENSE'),   1, 0, 'C', true); // Correction accent
$pdf->Cell($col[3], 9, utf8_decode('BÉNÉFICE'),  1, 0, 'C', true); // Correction accent
$pdf->Ln(); // Retour à la ligne manuel

// ── LIGNES DONNÉES ─────────────────────────────────────────────────────────
$pdf->SetFont('Arial', '', 9);
$pdf->SetDrawColor(200, 200, 210);
$pdf->SetLineWidth(0.2);

foreach ($mois as $i => $m) {
    $isBenefPositif = $benef[$i] >= 0;

    // Fond alterné
    if ($i % 2 === 0) {
        $pdf->SetFillColor(GRIS_R, GRIS_G, GRIS_B);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    $pdf->SetX($startX); // On se place au centre pour chaque nouvelle ligne

    // Colonne Mois (avec correction d'accent pour Fév, Août, Déc)
    $pdf->SetTextColor(BLEU_R, BLEU_G, BLEU_B);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($col[0], 8, utf8_decode(strtoupper($m)), 1, 0, 'C', true);

    // Vente
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Cell($col[1], 8, number_format($vente[$i],   2, ',', ' ') . ' EUR', 1, 0, 'R', true);

    // Dépense
    $pdf->Cell($col[2], 8, number_format($depense[$i], 2, ',', ' ') . ' EUR', 1, 0, 'R', true);

    // Bénéfice
    if ($isBenefPositif) {
        $pdf->SetTextColor(0, 130, 60);
    } else {
        $pdf->SetTextColor(200, 30, 30);
    }
    $prefix = $isBenefPositif ? '+' : '';
    // On met '0' au lieu de '1' à la fin et on gère le saut de ligne en dessous
    $pdf->Cell($col[3], 8, $prefix . number_format($benef[$i], 2, ',', ' ') . ' EUR', 1, 0, 'R', true);
    $pdf->Ln(); // Retour à la ligne manuel
}

// ── LIGNE TOTAL ────────────────────────────────────────────────────────────
$totalVente   = array_sum($vente);
$totalDepense = array_sum($depense);
$totalBenef   = array_sum($benef);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(OR_R, OR_G, OR_B);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(OR_R, OR_G, OR_B);

$pdf->SetX($startX); // On se place au centre
$pdf->Cell($col[0], 9, 'TOTAL',                                                  1, 0, 'C', true);
$pdf->Cell($col[1], 9, number_format($totalVente,   2, ',', ' ') . ' EUR',       1, 0, 'R', true);
$pdf->Cell($col[2], 9, number_format($totalDepense, 2, ',', ' ') . ' EUR',       1, 0, 'R', true);
$pdf->Cell($col[3], 9, number_format($totalBenef,   2, ',', ' ') . ' EUR',       1, 0, 'R', true);
$pdf->Ln();


// ── SORTIE ─────────────────────────────────────────────────────────────────
$pdf->Output('D', 'rapport_financier_nancy.pdf');