<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT p.idproduit, p.nomProduit, p.Prix, p.image,
               p.idSousCategorie,
               sc.nomSousCategorie,
               sc.idCategorie,
               c.nomCategorie,
               s.Quantite AS quantite,
               s.datePeremption
        FROM Produit p
        INNER JOIN Sous_Categorie sc ON p.idSousCategorie = sc.idSousCategorie
        INNER JOIN Categorie c ON sc.idCategorie = c.idCategorie
        INNER JOIN Stock s ON p.idproduit = s.idProduit";

$params = [];
if (!empty($_GET['categorie'])) {
    $sql .= " WHERE c.idCategorie = :categorie";
    $params[':categorie'] = $_GET['categorie'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($produits);
?>