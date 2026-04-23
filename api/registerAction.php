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

        if (strlen($mdp) < 8) {
            echo json_encode(['error' => 'Le mot de passe doit faire au moins 8 caractères.']);
            exit();
        }
        if (!preg_match('/[A-Z]/', $mdp)) {
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins une majuscule.']);
            exit();
        }
        if (!preg_match('/[a-z]/', $mdp)) {
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins une minuscule.']);
            exit();
        }
        if (!preg_match('/[0-9]/', $mdp)) {
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins un chiffre.']);
            exit();
        }
        if (!preg_match('/[\W_]/', $mdp)) {
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins un caractère spécial.']);
            exit();
        }

        
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