<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT c.idCommande, c.numTicket, c.date, c.Montant, 
               lc.quantite, p.nomProduit, p.Prix, p.image, p.typeCategorie
        FROM Commande c
        INNER JOIN ligne_commande lc ON c.idCommande = lc.idCommande
        INNER JOIN Produit p ON lc.idproduit = p.idproduit
        WHERE c.etatPreparation = 0 AND DATE(c.date) = CURDATE()
        ORDER BY c.date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$commandes = [];
foreach ($lignes as $ligne) {
    $id = $ligne['idCommande'];
    
    if (!isset($commandes[$id])) {
        $commandes[$id] = [
            'idCommande' => $id,
            'numTicket' => $ligne['numTicket'],
            'date' => $ligne['date'],
            'totalItems' => 0,
            'produits' => []
        ];
    }
    
    $commandes[$id]['produits'][] = [
        'nom' => $ligne['nomProduit'],
        'quantite' => $ligne['quantite'],
        'prix' => $ligne['Prix'],
        'image' => $ligne['image'],
        'type' => $ligne['typeCategorie']
    ];
    $commandes[$id]['totalItems'] += $ligne['quantite'];
}

echo json_encode(['commandes' => array_values($commandes)]);
?>