<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $stmtEvents = $pdo->query("
        SELECT idEvenement, nomEvenement, dateEvenement 
        FROM evenement 
        ORDER BY dateEvenement ASC, idEvenement ASC
    ");
    $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

    $stmtConso = $pdo->query("
        SELECT 
            c.idEvenement, 
            p.nomProduit, 
            SUM(lc.quantite) as qteConsommee, 
            SUM(lc.quantite * lc.prix) as caProduit
        FROM commande c
        JOIN ligne_commande lc ON c.idCommande = lc.idCommande
        JOIN produit p ON lc.idproduit = p.idproduit
        WHERE c.idEvenement IS NOT NULL
        GROUP BY c.idEvenement, p.idproduit
    ");
    $consos = $stmtConso->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($events as $event) {
        $eventId = $event['idEvenement'];
        $eventData = [
            'id' => $eventId,
            'nom' => $event['nomEvenement'],
            'date' => $event['dateEvenement'],
            'consos' => [],
            'caTotal' => 0
        ];

        foreach ($consos as $conso) {
            if ($conso['idEvenement'] == $eventId) {
                $eventData['consos'][] = [
                    'produit' => $conso['nomProduit'],
                    'qte' => $conso['qteConsommee'],
                    'ca' => $conso['caProduit']
                ];
                $eventData['caTotal'] += $conso['caProduit'];
            }
        }
        $result[] = $eventData;
    }

    echo json_encode($result);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}