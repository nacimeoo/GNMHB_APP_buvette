<?php
header('Content-Type: application/json');
require 'db.php'; 

$sql = "SELECT p.idproduit, p.nomProduit, p.Prix, p.Image, p.idSousCategorie,
               s.Quantite as quantite, s.datePeremption,
               e.idEntrepot, e.nom as nomEntrepot,
               sc.nomSousCategorie
        FROM produit p
        JOIN stock s ON p.idproduit = s.idProduit
        JOIN entrepot e ON s.idEntrepot = e.idEntrepot
        LEFT JOIN sous_categorie sc ON p.idSousCategorie = sc.idSousCategorie";

$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($produits);
?>