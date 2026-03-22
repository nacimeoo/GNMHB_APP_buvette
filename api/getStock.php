<?php
header('Content-Type: application/json');
require 'db.php'; 

    $query = $pdo->query("SELECT * FROM Produit");
    $produits = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($produits);
?>