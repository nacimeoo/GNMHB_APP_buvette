<?php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("
    SELECT
        m.dateMouvement,
        m.typeMouvement,
        p.nomProduit,
        m.quantite,
        e.nom         AS nomEntrepot,
        m.motif,
        CONCAT(u.prenom, ' ', u.nom) AS operateur
    FROM MouvementStock m
    JOIN Stock      s ON m.idStock       = s.idStock
    JOIN Produit    p ON s.idProduit     = p.idproduit
    JOIN Entrepot   e ON s.idEntrepot    = e.idEntrepot
    JOIN Utilisateur u ON m.idUtilisateur = u.id
    ORDER BY m.dateMouvement DESC
    LIMIT 200
");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
