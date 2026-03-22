<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT * FROM Commande v WHERE MONTH(v.date) = MONTH(CURRENT_DATE()) AND YEAR(v.date) = YEAR(CURRENT_DATE())";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['commandes' => $ventes]);
?>