<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['active_event_id'])) {
    $stmtEvent = $pdo->query("SELECT idEvenement, idEntrepot FROM evenement WHERE statut = 'En cours' LIMIT 1");
    $activeEvent = $stmtEvent->fetch(PDO::FETCH_ASSOC);
    
    if ($activeEvent) {
        $_SESSION['active_event_id'] = $activeEvent['idEvenement'];
        $_SESSION['active_entrepot_id'] = $activeEvent['idEntrepot'];
    } else {
        echo json_encode([]);
        exit;
    }
}

$idEntrepotActif = $_SESSION['active_entrepot_id'] ?? 1;

$sql = "SELECT p.idproduit, p.nomProduit, p.idSousCategorie, p.typeCategorie, p.Prix, p.seuil_alerte,
               p.Image AS image,
               sc.nomSousCategorie, c.nomCategorie,
               COALESCE((SELECT SUM(s.Quantite) FROM stock s WHERE s.idProduit = p.idproduit AND s.idEntrepot = :idEntrepotActif), 0) AS quantiteStock
        FROM produit p
        INNER JOIN sous_categorie sc ON p.idSousCategorie = sc.idSousCategorie
        INNER JOIN categorie c ON sc.idCategorie = c.idCategorie
        WHERE p.typeCategorie != 'Matiere_Premiere'";

$params = [':idEntrepotActif' => $idEntrepotActif];

if (!empty($_GET['categorie'])) {
    $sql .= " AND c.idCategorie = :categorie";
    $params[':categorie'] = $_GET['categorie'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtCompo = $pdo->prepare("
    SELECT comp.qte, 
           COALESCE((SELECT SUM(Quantite) FROM stock WHERE idProduit = comp.idIngredient AND idEntrepot = :idEntrepotActif), 0) as stock_ing
    FROM composition comp
    WHERE comp.idProduit = :idProd
");

foreach ($produits as &$prod) {
    if (strtolower($prod['typeCategorie']) === 'compose') {
        
        $stmtCompo->execute([
            ':idProd'          => $prod['idproduit'],
            ':idEntrepotActif' => $idEntrepotActif
        ]);
        $ingredients = $stmtCompo->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($ingredients) > 0) {
            $maxFaisable = null;
            
            foreach ($ingredients as $ing) {
                $possible = floor($ing['stock_ing'] / $ing['qte']);
                if ($maxFaisable === null || $possible < $maxFaisable) {
                    $maxFaisable = $possible;
                }
            }
            $prod['quantiteStock'] = $maxFaisable;
        } else {
            $prod['quantiteStock'] = 0;
        }
    }
}

echo json_encode($produits);
?>