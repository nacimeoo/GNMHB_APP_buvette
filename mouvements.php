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
<html lang="fr">
<head>
  <title>GNMHB – Mouvements de stock</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/vendor.css">
  <link rel="stylesheet" type="text/css" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    /* ── Required asterisk ── */
    .lbl-req::after { content: ' *'; color: #e74c3c; }

    /* ── Product lines ── */
    .ligne-produit {
      background: #f8f9fb;
      border: 1.5px solid #e8eaf0;
      border-radius: 10px;
      padding: 14px 16px;
      margin-bottom: 10px;
    }
    .ligne-produit:last-child { margin-bottom: 0; }

    /* ── Autocomplete dropdown ── */
    .dropdown-auto {
      position: absolute;
      top: 100%; left: 0;
      width: 100%;
      z-index: 9999;
      display: none;
      max-height: 220px;
      overflow-y: auto;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .dropdown-auto .list-group-item { cursor: pointer; font-size: .88rem; }
    .dropdown-auto .list-group-item:hover { background: #eef1fb; }
    .dropdown-auto .item-nouveau {
      font-weight: 600;
      color: #1a2235;
      background: #fffbf0;
      border-top: 1px dashed #c9a84c;
    }
    .dropdown-auto .item-nouveau:hover { background: #fdf4d9; }

    /* ── Info produit sélectionné ── */
    .info-produit-connu {
      background: #eaf3ff;
      border: 1px solid #b8d4f5;
      border-radius: 6px;
      padding: 6px 10px;
      margin-top: 6px;
      font-size: .82rem;
    }

    /* ── Nouveau produit panel ── */
    .nouveau-produit-panel {
      background: #fffbf0;
      border: 1.5px solid #c9a84c;
      border-radius: 8px;
      padding: 14px;
      margin-top: 8px;
    }
    .nouveau-produit-panel .panel-title {
      font-size: .78rem;
      font-weight: 700;
      color: #b5943e;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 10px;
    }

    /* ── Total row ── */
    .total-row {
      background: #f8f9fb;
      border: 1.5px solid #e8eaf0;
      border-radius: 8px;
      padding: 14px 18px;
      margin-top: 18px;
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .total-row label { font-weight: 700; color: #1a2235; margin: 0; white-space: nowrap; }
    .total-row input { max-width: 180px; }

    /* ── Buttons ── */
    .btn-navy {
      background: #1a2235;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 9px 24px;
      font-weight: 700;
      font-size: .88rem;
    }
    .btn-navy:hover:not(:disabled) { background: #243154; color: #fff; }
    .btn-navy:disabled { background: #9aa0b5; cursor: not-allowed; }

    .btn-gold {
      background: #c9a84c;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 9px 24px;
      font-weight: 700;
      font-size: .88rem;
    }
    .btn-gold:hover:not(:disabled) { background: #b5943e; color: #fff; }
    .btn-gold:disabled { background: #d8c38b; cursor: not-allowed; }

    /* ── Badges mouvements ── */
    .badge-mvt {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .3px;
    }
    .badge-ENTREE    { background: #d4edda; color: #155724; }
    .badge-SORTIE    { background: #f8d7da; color: #721c24; }
    .badge-PERTE     { background: #fff3cd; color: #856404; }
    .badge-CORRECTIF { background: #d1ecf1; color: #0c5460; }

    /* ── Toast ── */
    .mvt-toast {
      position: fixed;
      top: 22px; right: 22px;
      z-index: 99999;
      min-width: 300px;
      max-width: 420px;
      padding: 13px 18px;
      border-radius: 10px;
      font-weight: 600;
      font-size: .9rem;
      display: none;
      align-items: center;
      gap: 10px;
      box-shadow: 0 6px 24px rgba(0,0,0,.14);
      animation: slideIn .2s ease;
    }
    .mvt-toast--success { background: #e9f7ef; color: #1a7a45; border-left: 4px solid #27ae60; }
    .mvt-toast--error   { background: #fdecea; color: #922b21; border-left: 4px solid #e74c3c; }
    @keyframes slideIn { from { transform: translateX(28px); opacity:0; } to { transform: translateX(0); opacity:1; } }

    /* ── Column header hint (desktop) ── */
    .lignes-header {
      display: grid;
      grid-template-columns: 1fr 100px 160px 44px;
      gap: 10px;
      padding: 0 16px 6px;
      font-size: .72rem;
      font-weight: 700;
      color: #8a8fa8;
      text-transform: uppercase;
      letter-spacing: .5px;
    }
    @media (max-width: 767px) { .lignes-header { display: none; } }
  </style>
</head>
<body>

  <!-- ── Sidebar ── -->
  <aside class="admin-sidebar">
    <div class="sidebar-logo">
      <img src="images/logoClub.png" alt="Logo Club">
    </div>
    <nav class="sidebar-nav">
      <a href="index.php"       class="sidebar-link"        title="Accueil"><i class="bi bi-house-door-fill"></i></a>
      <a href="commande.php"    class="sidebar-link"        title="Commandes"><i class="bi bi-receipt-cutoff"></i></a>
      <a href="stock.php"       class="sidebar-link"        title="Stock"><i class="bi bi-box-seam-fill"></i></a>
      <a href="mouvements.php"  class="sidebar-link active" title="Mouvements"><i class="bi bi-arrow-left-right"></i></a>
      <a href="vente.php"       class="sidebar-link"        title="Vente"><i class="bi bi-cart-fill"></i></a>
      <a href="user.php"        class="sidebar-link"        title="Utilisateurs"><i class="bi bi-person-circle"></i></a>
      <a href="statistique.php" class="sidebar-link"        title="Statistiques"><i class="bi bi-bar-chart-fill"></i></a>

      <div class="dropup d-md-none">
        <button class="sidebar-user-btn" id="userMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle"></i>
        </button>
        <ul class="dropdown-menu user-mini-modal" aria-labelledby="userMenuMobile">
          <?php if (isset($_SESSION['user_id'])): ?>
          <li class="user-mini-modal__info"><i class="bi bi-person-fill me-2"></i><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></li>
          <li><hr class="dropdown-divider m-0"></li>
          <li><a class="dropdown-item text-danger" href="api/deco.php"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </nav>
    <div class="sidebar-bottom">
      <div class="dropup">
        <button class="sidebar-user-btn" id="userMenuDesktop" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle"></i>
        </button>
        <ul class="dropdown-menu user-mini-modal" aria-labelledby="userMenuDesktop">
          <?php if (isset($_SESSION['user_id'])): ?>
          <li class="user-mini-modal__info"><i class="bi bi-person-fill me-2"></i><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></li>
          <li><hr class="dropdown-divider m-0"></li>
          <li><a class="dropdown-item text-danger" href="api/deco.php"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </aside>

  <!-- ── Toast notification ── -->
  <div class="mvt-toast" id="mvt-toast"></div>


  <!-- ═══════════════════════════════════════════════
       SECTION 1 — ENTRÉE DE STOCK
  ═══════════════════════════════════════════════ -->
  <div class="stock-table-wrapper">
    <div class="stock-table-header">
      <h5 class="stock-table-title">
        <i class="bi bi-box-arrow-in-down me-2"></i>Entrée de stock
      </h5>
    </div>

    <div class="p-4">
      <form id="form-entree" autocomplete="off" novalidate>

        <!-- En-tête : Entrepôt + Date -->
        <div class="row g-3 mb-4">
          <div class="col-sm-5 col-md-4">
            <label class="form-label fw-semibold lbl-req" for="entree-entrepot">Entrepôt</label>
            <select class="form-select" id="entree-entrepot" required>
              <option value="">— Sélectionner —</option>
            </select>
          </div>
          <div class="col-sm-4 col-md-3">
            <label class="form-label fw-semibold lbl-req" for="entree-date">Date</label>
            <input type="date" class="form-control" id="entree-date" required>
          </div>
        </div>

        <!-- En-têtes colonnes (desktop) -->
        <div class="lignes-header">
          <span>Produit <span style="color:#e74c3c">*</span></span>
          <span>Quantité <span style="color:#e74c3c">*</span></span>
          <span>Date de péremption <span style="color:#e74c3c">*</span></span>
          <span></span>
        </div>

        <!-- Lignes produits -->
        <div id="entree-lignes"></div>

        <button type="button" class="btn btn-outline-secondary btn-sm mt-3" id="btn-ajouter-ligne">
          <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
        </button>

        <!-- Total dépensé -->
        <div class="total-row">
          <label for="entree-montant"><i class="bi bi-receipt me-1"></i>Total dépensé (€)</label>
          <input type="number" min="0" step="0.01" class="form-control" id="entree-montant" placeholder="0.00">
        </div>

        <div class="mt-4">
          <button type="button" class="btn btn-navy" id="btn-valider-entree">
            <i class="bi bi-check2-circle me-2"></i>Valider l'entrée
          </button>
        </div>

      </form>
    </div>
  </div>


  <!-- ═══════════════════════════════════════════════
       SECTION 2 — DÉCLARER UNE PERTE
  ═══════════════════════════════════════════════ -->
  <div class="stock-table-wrapper">
    <div class="stock-table-header">
      <h5 class="stock-table-title">
        <i class="bi bi-exclamation-triangle me-2"></i>Déclarer une perte
      </h5>
    </div>

    <div class="p-4">
      <form id="form-perte" autocomplete="off" novalidate>
        <div class="row g-3 align-items-end">
          <div class="col-sm-6 col-md-4">
            <label class="form-label fw-semibold lbl-req" for="perte-produit">Produit</label>
            <select class="form-select" id="perte-produit" required>
              <option value="">— Sélectionner —</option>
            </select>
          </div>
          <div class="col-sm-5 col-md-3">
            <label class="form-label fw-semibold lbl-req" for="perte-entrepot">Entrepôt</label>
            <select class="form-select" id="perte-entrepot" required>
              <option value="">— Sélectionner —</option>
            </select>
          </div>
          <div class="col-sm-4 col-md-2">
            <label class="form-label fw-semibold lbl-req" for="perte-quantite">Quantité</label>
            <input type="number" min="1" class="form-control" id="perte-quantite" placeholder="0" required>
          </div>
          <div class="col-sm-auto col-md-3">
            <button type="button" class="btn btn-gold w-100" id="btn-valider-perte">
              <i class="bi bi-exclamation-triangle me-2"></i>Déclarer la perte
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>


  <!-- ═══════════════════════════════════════════════
       SECTION 3 — HISTORIQUE
  ═══════════════════════════════════════════════ -->
  <div class="stock-table-wrapper">
    <div class="stock-table-header">
      <h5 class="stock-table-title">
        <i class="bi bi-clock-history me-2"></i>Historique des mouvements
      </h5>
      <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-historique" title="Actualiser">
        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
      </button>
    </div>

    <div class="table-responsive">
      <table class="table stock-table align-middle mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Produit</th>
            <th>Qté</th>
            <th>Entrepôt</th>
            <th>Opérateur</th>
          </tr>
        </thead>
        <tbody id="historique-body">
          <tr><td colspan="6" class="text-center text-muted py-4">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </div>


  <script src="js/jquery-1.11.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  <script src="js/mouvements.js"></script>
</body>
</html>
