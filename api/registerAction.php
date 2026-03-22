<?php
session_start(); 
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['name'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mdp = $_POST['password'] ?? '';
    $role = 'EN_ATTENTE';
    $tel = NULL;

    if (!empty($prenom) && !empty($nom) && !empty($email) && !empty($mdp)) {

        $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, prenom, email, mdp, role, telephone) VALUES (:nom, :prenom, :email, :mdp, :role, :telephone)");
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'mdp' => $hashed_password,
                'role' => $role,
                'telephone' => $tel
            ]);

            header('Location: ../login.html');
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "Erreur : Cette adresse email est déjà utilisée.";
            } else {
                echo "Erreur lors de l'inscription : " . $e->getMessage();
            }
        }
    } else {
        echo "Erreur : Veuillez remplir tous les champs.";
    }
} else {
    echo "Méthode non autorisée.";
}