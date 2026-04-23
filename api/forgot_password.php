<?php
session_start();
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $stmt = $pdo->prepare("SELECT id FROM Utilisateur WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32)); 
            $expire_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
            $deleteStmt->execute(['email' => $email]);

            $insertStmt = $pdo->prepare("INSERT INTO password_resets (email, token, expire_at) VALUES (:email, :token, :expire_at)");
            $insertStmt->execute([
                'email' => $email,
                'token' => $token,
                'expire_at' => $expire_at
            ]);


            $resetLink = "http://localhost/GNMHB/api/reset_password.php?token=" . $token;  //chnagrer plus tard mettre vrai lien

            $sujet = "Réinitialisation de votre mot de passe - GNMHB";
            $message = "Bonjour,\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant (valable 1 heure) :\n" . $resetLink;
            $headers = "From: noreply@gnmhb.fr"; // chnager plus tard mettre vrai mail
            
            //test sans email
            die("tests : <a href='$resetLink'>Cliquez ici pour réinitialiser</a>");
        }

        $_SESSION['success'] = "Si cette adresse existe, un email de réinitialisation a été envoyé.";
        header('Location: ../forgot.html'); 
        exit();

    } else {
        echo "Adresse email invalide.";
    }
} else {
    echo "Méthode non autorisée.";
}