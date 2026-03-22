<?php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("SELECT idEntrepot, nom FROM Entrepot ORDER BY nom");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
