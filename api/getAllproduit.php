<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT p.idproduit, p.nomProduit, p.idSousCategorie, p.typeCategorie, p.Prix, p.seuil_alerte,
               p.Image AS image,
               sc.nomSousCategorie, c.nomCategorie,
               COALESCE((SELECT SUM(s.Quantite) FROM Stock s WHERE s.idProduit = p.idproduit), 0) AS quantiteStock
        FROM Produit p
        INNER JOIN Sous_Categorie sc ON p.idSousCategorie = sc.idSousCategorie
        INNER JOIN Categorie c ON sc.idCategorie = c.idCategorie
        WHERE p.typeCategorie != 'Matiere_Premiere'";

$params = [];
if (!empty($_GET['categorie'])) {
    $sql .= " AND c.idCategorie = :categorie";
    $params[':categorie'] = $_GET['categorie'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>