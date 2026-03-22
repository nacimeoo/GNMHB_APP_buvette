<?php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("SELECT p.idproduit, p.nomProduit FROM Produit p ORDER BY p.nomProduit ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
