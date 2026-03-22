<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

if ($_SESSION['user_statut'] === 'valide') {
    header('Location: index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Compte en attente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="text-center">
        <h1 class="text-warning"><i class="bi bi-hourglass-split"></i></h1>
        <h2>Compte en attente de validation</h2>
        <p class="text-muted">Un administrateur doit valider votre compte avant que vous puissiez accéder a l'application.</p>
        <a href="api/deco.php" class="btn btn-secondary mt-3">Se déconnecter</a>
    </div>
</body>
</html>