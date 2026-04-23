<?php
header('Content-Type: application/json');
require 'db.php';

$impayeOnly = isset($_GET['impaye']) && $_GET['impaye'] == '1';

$sql = "SELECT * FROM Commande v WHERE MONTH(v.date) = MONTH(CURRENT_DATE()) AND YEAR(v.date) = YEAR(CURRENT_DATE())";

if ($impayeOnly) {
    $sql .= " AND etatPaiement = 0";
}

$sql .= " ORDER BY date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['commandes' => $ventes]);
?>