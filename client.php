<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

if ($_SESSION['user_statut'] === 'en_attente') {
    header('Location: attente.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>GNMHB</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <meta name="apple-mobile-web-app-capable" content="yes">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="client-body">

  <!-- Panier offcanvas -->
  <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasCart">
    <div class="cart-offcanvas-header">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-cart-fill"></i>
        <span class="cart-offcanvas-title">Mon Panier</span>
      </div>
      <button class="cart-close-btn" data-bs-dismiss="offcanvas" aria-label="Fermer">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
      <ul class="cart-item-list flex-grow-1"></ul>
      <div class="cart-footer">
        <div class="cart-total-row">
          <span>Total</span>
          <span class="cart-total-row__value">0.00 €</span>
        </div>
        <button class="cart-pay-btn" onclick="validerPanier()">
          <i class="bi bi-credit-card-fill"></i> Payer
        </button>
      </div>
    </div>
  </div>

  <!-- Contenu principal -->
  <div class="app-wrap">

    <!-- VUE : Catégories -->
    <div id="vue-categories">
      <div class="cat-intro">
        <img src="images/logoClub.png" alt="GNMHB" onerror="this.style.display='none'">
        <h1>Que souhaitez-vous ?</h1>
        <p>Choisissez une catégorie pour commencer</p>
      </div>

      <div class="cat-grid">
        <button class="cat-card" onclick="ouvrirCategorie(1, 'Boisson')">
          <img src="images/coca.png" alt="Boisson" class="cat-card-img">
          <div class="cat-card-overlay">
            <span class="cat-name">Boisson</span>
          </div>
        </button>

        <button class="cat-card" onclick="ouvrirCategorie(2, 'Petite faim')">
          <img src="images/croque.png" alt="Petite faim" class="cat-card-img">
          <div class="cat-card-overlay">
            <span class="cat-name">Petite faim</span>
          </div>
        </button>

        <button class="cat-card" onclick="ouvrirCategorie(3, 'Snacks')">
          <img src="images/snickers.png" alt="Snacks" class="cat-card-img">
          <div class="cat-card-overlay">
            <span class="cat-name">Snacks</span>
          </div>
        </button>

        <button class="cat-card" onclick="ouvrirCategorie(4, 'Gourmandise')">
          <img src="images/gaufre.png" alt="Gourmandise" class="cat-card-img">
          <div class="cat-card-overlay">
            <span class="cat-name">Gourmandise</span>
          </div>
        </button>
      </div>
    </div>

    <!-- VUE : Produits -->
    <div id="vue-produits">
      <div class="back-bar">
        <button class="back-btn" onclick="retourCategories()" aria-label="Retour">
          <i class="bi bi-arrow-left"></i>
        </button>
        <h2 id="titre-categorie"></h2>
      </div>
      <div id="sous-cat-chips" class="subcat-chips-wrapper"></div>
      <div id="liste-produits"></div>
    </div>

  </div><!-- /.app-wrap -->

  <!-- Navigation bas -->
  <nav class="bottom-nav">
    <button class="bottom-nav-btn active" id="btn-home" onclick="retourCategories()">
      <i class="bi bi-house-fill"></i>
      <span>Accueil</span>
    </button>
    <button class="bottom-nav-btn" id="btn-cart" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
      <i class="bi bi-cart-fill"></i>
      <span>Panier</span>
      <span class="cart-badge cart-count-badge">0</span>
    </button>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/client.js"></script>
  <script src="js/panier.js"></script>
</body>
</html>
