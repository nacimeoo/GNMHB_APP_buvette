<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pages_benevole = ['index.php', 'commande.php', 'stock.php'];
$pages_admin    = ['index.php', 'vente.php', 'stock.php', 'commande.php', 'mouvements.php', 'user.php', 'statistique.php'];

$page_actuelle = basename($_SERVER['PHP_SELF']);

function verifierAcces($pages_autorisees) {
    global $page_actuelle;

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html');
        exit();
    }

    if ($_SESSION['user_statut'] === 'en_attente') {
        header('Location: attente.php');
        exit();
    }

    if (!in_array($page_actuelle, $pages_autorisees)) {
        header('Location: index.php');
        exit();
    }
}

switch ($_SESSION['user_role'] ?? '') {
    case 'admin':
        verifierAcces($pages_admin);
        break;
    case 'benevole':
        verifierAcces($pages_benevole);
        break;
    default:
        header('Location: login.html');
        exit();
}