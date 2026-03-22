<?php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("
    SELECT sc.idSousCategorie, sc.nomSousCategorie, c.nomCategorie
    FROM Sous_Categorie sc
    JOIN Categorie c ON sc.idCategorie = c.idCategorie
    ORDER BY c.nomCategorie, sc.nomSousCategorie
");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
