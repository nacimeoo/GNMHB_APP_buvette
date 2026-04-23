<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

require_once 'db.php'; 

$id     = intval($_POST['id'] ?? 0);
$prenom = trim($_POST['prenom'] ?? '');
$nom    = trim($_POST['nom'] ?? '');
$email  = trim($_POST['email'] ?? '');
$role   = trim($_POST['role'] ?? '');

if (!$id || !$prenom || !$nom || !$email || !$role) {
    echo json_encode(['success' => false, 'message' => 'Champs manquants']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE utilisateur SET prenom = ?, nom = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$prenom, $nom, $email, $role, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}