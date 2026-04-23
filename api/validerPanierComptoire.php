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

$idUtilisateur = $_SESSION['user_id'] ?? null;
if (!$idUtilisateur) {
    echo json_encode(['success' => false, 'message' => 'Session expirée, veuillez vous reconnecter.']);
    exit;
}

$typePaiement = $data['type_paiement'] ?? 'CB';
$etatPaiement = ($typePaiement === 'comptoir') ? 0 : 1; 

$idEvenement = $_SESSION['active_event_id'] ?? null;
$idEntrepotActif = $_SESSION['active_entrepot_id'] ?? 1;

$nom_client    = $data['nom_client'] ?? '';
$prenom_client = $data['prenom_client'] ?? '';
$email_client  = $data['email_client'] ?? '';

try {
    $pdo->beginTransaction();

    $montantTotal = 0;
    foreach ($data['items'] as $item) {
        $montantTotal += floatval($item['prix']) * intval($item['quantite']);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Commande (numTicket, `date`, Montant, modePAIEMENT, etatPaiement, etatPreparation, idEvenement, idUtilisateur, nom_client, prenom_client, email_client)
         VALUES ('T-TEMP', NOW(), :montant, :modePaiement, :etatPaiement, 0, :evenement, :utilisateur, :nom_client, :prenom_client, :email_client)"
    );
    
    $stmt->execute([
        ':montant'       => round($montantTotal, 2),
        ':modePaiement'  => $typePaiement,
        ':etatPaiement'  => $etatPaiement,
        ':evenement'     => $idEvenement,
        ':utilisateur'   => $idUtilisateur,
        ':nom_client'    => $nom_client,
        ':prenom_client' => $prenom_client,
        ':email_client'  => $email_client,
    ]);

    $idCommande = $pdo->lastInsertId();
    $numTicket = 'T-' . $idCommande;

    $stmtUpdate = $pdo->prepare("UPDATE Commande SET numTicket = :numTicket WHERE idCommande = :idCmd");
    $stmtUpdate->execute([
        ':numTicket' => $numTicket,
        ':idCmd'     => $idCommande
    ]);

    $stmtLigne = $pdo->prepare(
        "INSERT INTO ligne_commande (idCommande, idproduit, quantite, prix) VALUES (:idCmd, :idProd, :qty, :prix)"
    );
    
    $stmtCheckCompo = $pdo->prepare("SELECT idIngredient, qte FROM composition WHERE idProduit = :idProd");
    
    $stmtStock = $pdo->prepare(
        "UPDATE Stock SET Quantite = GREATEST(0, Quantite - :qty) WHERE idProduit = :idProd AND idEntrepot = :idEntrepot"
    );

    foreach ($data['items'] as $item) {
        $stmtLigne->execute([
            ':idCmd'  => $idCommande,
            ':idProd' => $item['idproduit'],
            ':qty'    => $item['quantite'],
            ':prix'   => $item['prix'],
        ]);
        
        $stmtCheckCompo->execute([':idProd' => $item['idproduit']]);
        $ingredients = $stmtCheckCompo->fetchAll(PDO::FETCH_ASSOC);

        if (count($ingredients) > 0) {
            foreach ($ingredients as $ing) {
                $qteADeduire = $ing['qte'] * $item['quantite'];
                $stmtStock->execute([
                    ':qty'        => $qteADeduire,
                    ':idProd'     => $ing['idIngredient'],
                    ':idEntrepot' => $idEntrepotActif,
                ]);
            }
        } else {
            $stmtStock->execute([
                ':qty'        => $item['quantite'],
                ':idProd'     => $item['idproduit'],
                ':idEntrepot' => $idEntrepotActif,
            ]);
        }
    }

    $pdo->commit();

    if (!empty($email_client)) {

        require_once 'vendor/autoload.php';

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host     = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '9f01a67a6889be';
            $mail->Password = '25ba3a92f5b0f8';
            $mail->Port     = 2525;

            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom('boutique@gnmhb.fr', 'Nancy Handball');
            $mail->addAddress($email_client, $prenom_client . ' ' . $nom_client);

            $mail->isHTML(true);
            $mail->Subject = 'Votre Ticket de Caisse - Nancy Handball';

            $logoPath   = __DIR__ . '/../images/logoClub3.png';
            $logoTag    = '';
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
                $logoMime   = 'image/png';
                $logoTag    = "<img src='data:{$logoMime};base64,{$logoBase64}' alt='Nancy Handball' width='120' style='display:block; margin:0 auto 12px;'/>";
            }

            $articlesHtml = '';
            foreach ($data['items'] as $item) {
                $totalLigne = number_format($item['prix'] * $item['quantite'], 2, ',', ' ');
                $nomProduit = htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8');

                $articlesHtml .= "
                <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:12px;'>
                  <tr>
                    <td valign='top' style='padding-left:12px;'>
                      <p style='margin:0 0 4px; color:#222; font-size:13px; font-weight:bold;'>{$nomProduit}</p>
                      <p style='margin:0; color:#888; font-size:12px;'>qté : {$item['quantite']}</p>
                    </td>
                    <td valign='top' style='text-align:right; white-space:nowrap;'>
                      <p style='margin:0; color:#1a1f5e; font-size:14px; font-weight:bold;'>{$totalLigne} &euro;</p>
                    </td>
                  </tr>
                </table>
                <hr style='border:none; border-top:1px solid #f0f0f0; margin:0 0 12px;'/>
                ";
            }

            $montantAffiche = number_format($montantTotal, 2, ',', ' ');
            $dateAffichee   = date('d/m/Y H:i');
            $clientAffiche  = htmlspecialchars($prenom_client . ' ' . $nom_client, ENT_QUOTES, 'UTF-8');

            $mail->Body = "
            <!DOCTYPE html>
            <html lang='fr'>
            <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            </head>
            <body style='margin:0; padding:0; background-color:#f4f4f4; font-family:Arial, sans-serif;'>

            <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f4f4; padding:20px 0;'>
            <tr><td align='center'>

            <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:12px; overflow:hidden; max-width:560px;'>

                <!-- HEADER BLEU -->
                <tr>
                <td style='background-color:#1a1f5e; padding:30px 20px; text-align:center;'>
                    {$logoTag}
                </td>
                </tr>

                <!-- CHECK + TITRE -->
                <tr>
                <td style='padding:35px 30px 10px; text-align:center;'>
                    <div style='width:56px; height:56px; background:#f0faf4; border-radius:50%; margin:0 auto 16px; line-height:56px;'>
                    <span style='font-size:28px; color:#27ae60;'>&#10003;</span>
                    </div>
                    <h2 style='margin:0 0 8px; color:#1a1f5e; font-size:22px; font-weight:bold;'>Merci pour votre commande</h2>
                    <p style='margin:0; color:#888; font-size:13px;'>Veuillez vous rendre au comptoir pour payer votre commande</p>
                </td>
                </tr>

                <!-- SÉPARATEUR -->
                <tr><td style='padding:20px 30px 0;'><hr style='border:none; border-top:1px solid #eeeeee; margin:0;'/></td></tr>

                <!-- DÉTAILS TICKET -->
                <tr>
                <td style='padding:20px 30px;'>
                    <p style='margin:0 0 16px; color:#1a1f5e; font-size:15px; font-weight:bold;'>D&eacute;tail</p>
                    <table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td style='color:#888; font-size:13px; padding:8px 0; border-bottom:1px solid #f0f0f0;'>Ticket</td>
                        <td style='text-align:right; color:#222; font-size:13px; padding:8px 0; border-bottom:1px solid #f0f0f0;'>{$numTicket}</td>
                    </tr>
                    <tr>
                        <td style='color:#888; font-size:13px; padding:8px 0; border-bottom:1px solid #f0f0f0;'>Montant</td>
                        <td style='text-align:right; color:#222; font-size:13px; font-weight:bold; padding:8px 0; border-bottom:1px solid #f0f0f0;'>{$montantAffiche} &euro;</td>
                    </tr>
                    <tr>
                        <td style='color:#888; font-size:13px; padding:8px 0; border-bottom:1px solid #f0f0f0;'>Date</td>
                        <td style='text-align:right; color:#222; font-size:13px; padding:8px 0; border-bottom:1px solid #f0f0f0;'>{$dateAffichee}</td>
                    </tr>
                    <tr>
                        <td style='color:#888; font-size:13px; padding:8px 0;'>Client</td>
                        <td style='text-align:right; color:#222; font-size:13px; padding:8px 0;'>{$clientAffiche}</td>
                    </tr>
                    </table>
                </td>
                </tr>

                <!-- SÉPARATEUR -->
                <tr><td style='padding:0 30px;'><hr style='border:none; border-top:1px solid #eeeeee; margin:0;'/></td></tr>

                <!-- RÉCAPITULATIF -->
                <tr>
                <td style='padding:20px 30px;'>
                    <p style='margin:0 0 16px; color:#1a1f5e; font-size:15px; font-weight:bold;'>R&eacute;capitulatif de votre commande</p>
                    {$articlesHtml}
                </td>
                </tr>

                <!-- FOOTER BLEU -->
                <tr>
                <td style='background-color:#1a1f5e; padding:25px 20px; text-align:center;'>
                    <p style='margin:0 0 12px; color:#b4963c; font-size:13px; font-weight:bold; letter-spacing:1px;'>Besoin d&apos;aide ?</p>
                    <p style='margin:0; color:#aaaacc; font-size:12px;'>
                    &#9993; info@gnmhb.fr &nbsp;&nbsp; &#9742; +33 800 123 456
                    </p>
                    <p style='margin:16px 0 0; color:#666a99; font-size:11px;'>Grand Nancy M&eacute;tropole Handball</p>
                </td>
                </tr>

            </table>

            </td></tr>
            </table>
            </body>
            </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : " . $mail->ErrorInfo);
        }
    }

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