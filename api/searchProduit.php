<?php
header('Content-Type: application/json');
require 'db.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.idproduit, p.nomProduit, p.Prix, p.image, p.idSousCategorie, p.typeCategorie, p.seuil_alerte,
           sc.nomSousCategorie
    FROM Produit p
    INNER JOIN Sous_Categorie sc ON p.idSousCategorie = sc.idSousCategorie
    WHERE p.nomProduit LIKE :q
    ORDER BY p.nomProduit ASC
    LIMIT 10
");
$stmt->execute([':q' => '%' . $q . '%']);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
