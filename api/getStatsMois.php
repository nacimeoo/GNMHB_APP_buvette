<?php
header('Content-Type: application/json');
require_once 'db.php';

$stmt = $pdo->query("
    SELECT
        COUNT(*) AS nbCommandes,
        COALESCE(SUM(montant), 0) AS totalEncaisse,
        COALESCE(SUM(CASE WHEN modePAIEMENT = 'CB' THEN montant ELSE 0 END), 0) AS totalCB,
        COALESCE(SUM(CASE WHEN modePAIEMENT = 'Espece' THEN montant ELSE 0 END), 0) AS totalEspece,
        COALESCE(SUM(CASE WHEN modePAIEMENT = 'Credit' THEN montant ELSE 0 END), 0) AS totalImpayes
    FROM Commande
    WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())
");

echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
