<?php
session_start();
require 'db.php';

if (!isset($_GET['token'])) {
    die("Erreur : Aucun jeton de réinitialisation fourni.");
}

$token = $_GET['token'];


$stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = :token AND expire_at > NOW()");
$stmt->execute(['token' => $token]);
$resetRequest = $stmt->fetch();

if (!$resetRequest) {
    die("Erreur : Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.");
}

$email = $resetRequest['email'];
$erreur = null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $logoutDevices = isset($_POST['logout_devices']); 

    if ($password !== $passwordConfirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $erreur = "Le mot de passe doit faire au moins 8 caractères.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $updateStmt = $pdo->prepare("UPDATE Utilisateur SET mdp = :mdp WHERE email = :email");
        $updateStmt->execute([
            'mdp' => $hashed_password,
            'email' => $email
        ]);

        $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
        $deleteStmt->execute(['email' => $email]);

        header('Location: ../login.html');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Muhamad Nauval Azhar">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="This is a login page template based on Bootstrap 5">
    <title>Réinitialiser le mot de passe - GNMHB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <section class="h-100">
        <div class="container h-100">
            <div class="row justify-content-sm-center h-100">
                <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-7 col-sm-9">
                    <div class="text-center my-5">
                        <img src="../images/logoClub.png" alt="logo" width="100">
                    </div>
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <h1 class="fs-4 card-title fw-bold mb-4">Réinitialiser le mot de passe</h1>
                            
                            <?php if ($erreur): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($erreur) ?>
                                </div>
                            <?php endif; ?>

                            <form action="" method="POST" class="needs-validation" novalidate="" autocomplete="off">
                                <div class="mb-3">
                                    <label class="mb-2 text-muted" for="password">Nouveau mot de passe</label>
                                    <input id="password" type="password" class="form-control" name="password" required autofocus>
                                    <div class="invalid-feedback">
                                        Mot de passe requis 
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="mb-2 text-muted" for="password-confirm">Confirmer le mot de passe</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirm" required>
                                    <div class="invalid-feedback">
                                        Veuillez confirmer votre nouveau mot de passe
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <button type="submit" class="btn btn-primary ms-auto">
                                        Réinitialiser
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </body>
</html>