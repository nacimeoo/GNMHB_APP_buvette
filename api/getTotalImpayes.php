<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT SUM(v.montant) AS totalImpayes
        FROM Commande v
        WHERE MONTH(v.date) = MONTH(CURDATE()) AND YEAR(v.date) = YEAR(CURDATE()) AND v.modePAIEMENT = 'Comptoir' AND v.etatPaiement = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($result);

