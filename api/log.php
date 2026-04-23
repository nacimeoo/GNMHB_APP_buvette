<?php
session_start(); 
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mdp = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mdp, $user['mdp'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_statut'] = ($user['role'] === 'EN_ATTENTE') ? 'en_attente' : 'valide';

        header('Location: ../index.php'); 
        exit();

    } else {
        echo "Erreur : Email ou mot de passe incorrect. <a href='../login.html'>Réessayer</a>";    }
    } else {
    echo "Méthode non autorisée.";
}
?>