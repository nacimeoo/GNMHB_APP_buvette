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


if ($_SESSION['user_role'] === 'user') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>GNMHB</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  </head>
  <body>

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-logo">
        <img src="images/logoClub.png" alt="Logo Club">
      </div>
      <nav class="sidebar-nav">
        <a href="index.php" class="sidebar-link" title="Accueil">
          <i class="bi bi-house-door-fill"></i>
        </a>
        <a href="commande.php" class="sidebar-link" title="Commandes">
          <i class="bi bi-receipt-cutoff"></i>
        </a>
        <a href="stock.php" class="sidebar-link active" title="Stock">
          <i class="bi bi-box-seam-fill"></i>
        </a>
        <a href="mouvements.php" class="sidebar-link" title="Mouvements">
          <i class="bi bi-arrow-left-right"></i>
        </a>
        <a href="vente.php" class="sidebar-link" title="Vente">
          <i class="bi bi-cart-fill"></i>
        </a>
        
        <a href="user.php" class="sidebar-link" title="Utilisateurs">
          <i class="bi bi-person-circle"></i>
        </a>
        <a href="statistique.php" class="sidebar-link" title="Statistiques">
          <i class="bi bi-bar-chart-fill"></i>
        </a>
        
        <div class="dropup d-md-none">
          <button class="sidebar-user-btn" id="userMenuMobile" data-bs-toggle="dropdown" aria-expanded="false" title="Profil">
            <i class="bi bi-person-circle"></i>
          </button>
          <ul class="dropdown-menu user-mini-modal" aria-labelledby="userMenuMobile">
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="user-mini-modal__info"><i class="bi bi-person-fill me-2"></i><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></li>
            <li><hr class="dropdown-divider m-0"></li>
            <li><a class="dropdown-item text-danger" href="api/deco.php"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</a></li>
            <?php else: ?>
            <li><a class="dropdown-item text-success" href="login.html"><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
      <div class="sidebar-bottom">
        
        <div class="dropup">
          <button class="sidebar-user-btn" id="userMenuDesktop" data-bs-toggle="dropdown" aria-expanded="false" title="Profil">
            <i class="bi bi-person-circle"></i>
          </button>
          <ul class="dropdown-menu user-mini-modal" aria-labelledby="userMenuDesktop">
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="user-mini-modal__info"><i class="bi bi-person-fill me-2"></i><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></li>
            <li><hr class="dropdown-divider m-0"></li>
            <li><a class="dropdown-item text-danger" href="api/deco.php"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</a></li>
            <?php else: ?>
            <li><a class="dropdown-item text-success" href="login.html"><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </aside>

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
      <defs>
        <symbol xmlns="http://www.w3.org/2000/svg" id="link" viewBox="0 0 24 24">
          <path fill="currentColor" d="M12 19a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm5 0a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm0-4a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm-5 0a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm7-12h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3Zm1 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9h16Zm0-11H4V6a1 1 0 0 1 1-1h1v1a1 1 0 0 0 2 0V5h8v1a1 1 0 0 0 2 0V5h1a1 1 0 0 1 1 1ZM7 15a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm0 4a1 1 0 1 0-1-1a1 1 0 0 0 1 1Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="arrow-right" viewBox="0 0 24 24">
          <path fill="currentColor" d="M17.92 11.62a1 1 0 0 0-.21-.33l-5-5a1 1 0 0 0-1.42 1.42l3.3 3.29H7a1 1 0 0 0 0 2h7.59l-3.3 3.29a1 1 0 0 0 0 1.42a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .21-.33a1 1 0 0 0 0-.76Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="category" viewBox="0 0 24 24">
          <path fill="currentColor" d="M19 5.5h-6.28l-.32-1a3 3 0 0 0-2.84-2H5a3 3 0 0 0-3 3v13a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-10a3 3 0 0 0-3-3Zm1 13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-13a1 1 0 0 1 1-1h4.56a1 1 0 0 1 .95.68l.54 1.64a1 1 0 0 0 .95.68h7a1 1 0 0 1 1 1Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="calendar" viewBox="0 0 24 24">
          <path fill="currentColor" d="M19 4h-2V3a1 1 0 0 0-2 0v1H9V3a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3Zm1 15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-7h16Zm0-9H4V7a1 1 0 0 1 1-1h2v1a1 1 0 0 0 2 0V6h6v1a1 1 0 0 0 2 0V6h2a1 1 0 0 1 1 1Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="heart" viewBox="0 0 24 24">
          <path fill="currentColor" d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Zm-1.41 7.46L12 18.81l-6.75-6.74a4.28 4.28 0 0 1 3-7.3a4.25 4.25 0 0 1 3 1.25a1 1 0 0 0 1.42 0a4.27 4.27 0 0 1 6 6.05Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="plus" viewBox="0 0 24 24">
          <path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="minus" viewBox="0 0 24 24">
          <path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 24 24">
          <path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="check" viewBox="0 0 24 24">
          <path fill="currentColor" d="M18.71 7.21a1 1 0 0 0-1.42 0l-7.45 7.46l-3.13-3.14A1 1 0 1 0 5.29 13l3.84 3.84a1 1 0 0 0 1.42 0l8.16-8.16a1 1 0 0 0 0-1.47Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="trash" viewBox="0 0 24 24">
          <path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1ZM20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2ZM10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="star-outline" viewBox="0 0 15 15">
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M7.5 9.804L5.337 11l.413-2.533L4 6.674l2.418-.37L7.5 4l1.082 2.304l2.418.37l-1.75 1.793L9.663 11L7.5 9.804Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="star-solid" viewBox="0 0 15 15">
          <path fill="currentColor" d="M7.953 3.788a.5.5 0 0 0-.906 0L6.08 5.85l-2.154.33a.5.5 0 0 0-.283.843l1.574 1.613l-.373 2.284a.5.5 0 0 0 .736.518l1.92-1.063l1.921 1.063a.5.5 0 0 0 .736-.519l-.373-2.283l1.574-1.613a.5.5 0 0 0-.283-.844L8.921 5.85l-.968-2.062Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="search" viewBox="0 0 24 24">
          <path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="user" viewBox="0 0 24 24">
          <path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/>
        </symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="close" viewBox="0 0 15 15">
          <path fill="currentColor" d="M7.953 3.788a.5.5 0 0 0-.906 0L6.08 5.85l-2.154.33a.5.5 0 0 0-.283.843l1.574 1.613l-.373 2.284a.5.5 0 0 0 .736.518l1.92-1.063l1.921 1.063a.5.5 0 0 0 .736-.519l-.373-2.283l1.574-1.613a.5.5 0 0 0-.283-.844L8.921 5.85l-.968-2.062Z"/>
        </symbol>
      </defs>
    </svg>

    <div class="preloader-wrapper">
      <div class="preloader">
      </div>
    </div>

    
    

    

    <div class="stock-stats-grid">

      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--blue">
          <i class="bi bi-boxes"></i>
        </div>
        <div class="stat-card__body">
          <span class="stat-card__label">Total stock</span>
          <span class="stat-card__value" id="total-stock-valeur"></span>
          <span class="stat-card__sub">produits référencés</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="stat-card__body">
          <span class="stat-card__label">Stock bon</span>
          <span class="stat-card__value" id="stock-bon-valeur"></span>
          <span class="stat-card__sub">produits en bonne qté</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--orange">
          <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="stat-card__body">
          <span class="stat-card__label">Stock bas</span>
          <span class="stat-card__value" id="stock-bas-valeur"></span>
          <span class="stat-card__sub">produits à réapprovisionner</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--red">
          <i class="bi bi-x-circle-fill"></i>
        </div>
        <div class="stat-card__body">
          <span class="stat-card__label">Stock critique</span>
          <span class="stat-card__value" id="stock-critique-valeur"></span>
          <span class="stat-card__sub">produits en rupture imminente</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--gold">
          <i class="bi bi-cash-coin"></i>
        </div>
        <div class="stat-card__body">
          <span class="stat-card__label">Valeur du stock</span>
          <span class="stat-card__value" id="valeur-stock-valeur"></span>
          <span class="stat-card__sub">valeur totale du stock</span>
        </div>
      </div>

      

    </div>

    <div class="stock-table-wrapper">
      <div class="stock-table-header">
        <h5 class="stock-table-title">
          <i class="bi bi-box-seam-fill me-2"></i>Stock des produits
        </h5>
        <button class="btn btn-sm stock-btn-add" data-bs-toggle="modal" data-bs-target="#modalAjoutProduit">
          <i class="bi bi-plus-lg me-1"></i>Ajouter un produit
        </button>
      </div>

      <div class="table-responsive">
        <table class="table stock-table align-middle mb-0">
          <thead>
            <tr>
              <th>Image</th>
              <th>Produit</th>
              <th>Catégorie</th>
              <th>Prix de vente</th>
              <th>Quantité</th>
              <th>Date de péremption</th>
              <th>Statut</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="stock-produits">
            </tbody>
        </table>
      </div>

    

    </div>

    <div id="footer-bottom">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6 copyright">
            <p>© 2023 Foodmart. All rights reserved.</p>
          </div>
          <div class="col-md-6 credit-link text-start text-md-end">
            <p>Free HTML Template by <a href="https://templatesjungle.com/">TemplatesJungle</a> Distributed by <a href="https://themewagon">ThemeWagon</a></p>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalAjoutProduit" tabindex="-1" aria-labelledby="titreModalAjout" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title" id="titreModalAjout">Ajouter au stock</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>

          <div class="modal-body">
            <form id="form-ajout-produit" autocomplete="off">

              <!-- Recherche avec autocomplete -->
              <div class="mb-3 position-relative">
                <label for="nomProduit" class="form-label">Nom du produit</label>
                <input type="text" class="form-control" id="nomProduit" placeholder="Tapez pour rechercher..." autocomplete="off" required>
                <input type="hidden" id="idProduitCache">
                <ul id="autocomplete-list" class="list-group position-absolute w-100" style="z-index:9999;display:none;max-height:200px;overflow-y:auto;top:100%;left:0;"></ul>
              </div>

              <!-- Bandeau produit connu sélectionné -->
              <div id="info-produit-connu" class="mb-3 p-2 rounded border d-none" style="background:#f0f7ff;">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-check-circle-fill text-success"></i>
                  <span class="fw-semibold" id="info-produit-nom"></span>
                  <span class="text-muted ms-auto" id="info-produit-prix"></span>
                </div>
                <small class="text-muted" id="info-produit-cat"></small>
              </div>

              <!-- Champs uniquement pour un nouveau produit inconnu -->
              <div id="section-nouveau-produit" class="d-none">
                <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.85rem;">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>Produit non trouvé — il sera créé automatiquement.
                </div>
                <div class="mb-3">
                  <label for="categorieProduit" class="form-label">Sous-Catégorie</label>
                  <select class="form-select" id="categorieProduit">
                    <option value="" disabled selected>Choisir une sous-catégorie...</option>
                    <option value="1">Soft (Boissons froides)</option>
                    <option value="2">Chaud (Boissons chaudes)</option>
                    <option value="3">Plat (Hot-dog, croque monsieur)</option>
                    <option value="4">Sucré (Crêpes, Gaufres...)</option>
                    <option value="5">Chocolat (Barres chocolatées)</option>
                    <option value="6">Bonbon</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="typeProduit" class="form-label">Type de produit</label>
                  <select class="form-select" id="typeProduit">
                    <option value="Simple" selected>Produit Simple (ex: Coca, KitKat)</option>
                    <option value="Compose">Produit Composé (ex: Hot-Dog, Café)</option>
                    <option value="Matiere_Premiere">Matière Première (ex: Pain, Saucisse)</option>
                  </select>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="prixProduit" class="form-label">Prix de vente</label>
                    <input type="number" step="0.01" class="form-control" id="prixProduit">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="seuilProduit" class="form-label">Seuil d'alerte</label>
                    <input type="number" step="0.01" class="form-control" id="seuilProduit">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="imageProduit" class="form-label">URL de l'image</label>
                  <input type="text" class="form-control" id="imageProduit" placeholder="images/nouveau-produit.png">
                </div>
              </div>

              <!-- Toujours visibles -->
              <div class="mb-3">
                <label for="datePeremption" class="form-label">Date de péremption</label>
                <input type="date" class="form-control" id="datePeremption" required>
              </div>
              <div class="mb-3">
                <label for="quantiteProduit" class="form-label">Quantité</label>
                <input type="number" min="1" class="form-control" id="quantiteProduit" required>
              </div>

            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" id="btn-sauvegarder-produit">Enregistrer</button>
          </div>

        </div>
      </div>
    </div>

    <div class="modal fade" id="modalEditProduit" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Modifier le produit</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form id="form-edit-produit" action="api/updateProduit.php" method="POST">
              <input type="hidden" name="id" id="editIdProduit">
              
              <div class="mb-3">
                <label class="form-label">Nom du produit</label>
                <input type="text" class="form-control" name="nom" id="editNomProduit" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Catégorie (ID)</label>
                <input type="number" class="form-control" name="categorie_id" id="editCategorieProduit" required>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Prix (€)</label>
                  <input type="number" step="0.01" class="form-control" name="prix" id="editPrixProduit" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Quantité</label>
                  <input type="number" class="form-control" name="quantite_stock" id="editQuantiteProduit" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">URL de l'image</label>
                <input type="text" class="form-control" name="image_url" id="editImageProduit">
              </div>
              <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="js/plugins.js"></script>
    <script src="js/script.js"></script>
    <script src="js/stock.js"></script>
  </body>
</html>