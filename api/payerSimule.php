<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!isset($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Le panier est vide.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Calcul du total
    $montantTotal = 0;
    foreach ($data['items'] as $item) {
        $montantTotal += $item['prix'] * $item['quantite'];
    }

    $idUtilisateur = $_SESSION['user_id'] ?? null;
    if (!$idUtilisateur) {
        echo json_encode(['success' => false, 'message' => 'Session expirée, veuillez vous reconnecter.']);
        exit;
    }
    $idEvenement = null;

    // Création de la commande — etatPaiement = 1 (payé), etatPreparation = 0 (en attente)
    $stmt = $pdo->prepare(
        "INSERT INTO Commande (numTicket, `date`, Montant, modePAIEMENT, etatPaiement, etatPreparation, idEvenement, idUtilisateur)
         VALUES ('T-TEMP', NOW(), :montant, 'CB', 1, 0, :evenement, :utilisateur)"
    );
    $stmt->execute([
        ':montant'     => round($montantTotal, 2),
        ':evenement'   => $idEvenement,
        ':utilisateur' => $idUtilisateur,
    ]);

    // Récupérer le vrai ID généré par MySQL (plus fiable que lastInsertId())
    $idCommande = (int) $pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn();

    // Numéro de ticket final
    $pdo->exec("UPDATE Commande SET numTicket = 'T-$idCommande' WHERE idCommande = $idCommande");

    // Lignes de commande + décrémentation du stock
    $stmtLigne = $pdo->prepare(
        "INSERT INTO ligne_commande (idCommande, idproduit, quantite, prix) VALUES (:idCmd, :idProd, :qty, :prix)"
    );
    $stmtStock = $pdo->prepare(
        "UPDATE Stock SET Quantite = GREATEST(0, Quantite - :qty) WHERE idProduit = :idProd"
    );
    foreach ($data['items'] as $item) {
        $stmtLigne->execute([
            ':idCmd'  => $idCommande,
            ':idProd' => $item['idproduit'],
            ':qty'    => $item['quantite'],
            ':prix'   => $item['prix'],
        ]);
        $stmtStock->execute([
            ':qty'    => $item['quantite'],
            ':idProd' => $item['idproduit'],
        ]);
    }

    $pdo->commit();

    // Lire le numTicket réel depuis la BDD après commit (source de vérité)
    $row = $pdo->prepare("SELECT numTicket FROM Commande WHERE idCommande = ?");
    $row->execute([$idCommande]);
    $numTicket = $row->fetchColumn() ?: ('T-' . $idCommande);

    echo json_encode([
        'success'    => true,
        'idCommande' => $idCommande,
        'numTicket'  => $numTicket,
        'montant'    => number_format($montantTotal, 2, '.', ''),
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
}
?>
