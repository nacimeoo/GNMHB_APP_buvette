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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="page-index">

    <svg xmlns="http://www.w3.org/2000/svg" style="display:none;">
        <defs>
            <symbol id="plus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/></symbol>
            <symbol id="minus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/></symbol>
            <symbol id="trash" viewBox="0 0 24 24"><path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1ZM20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2ZM10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1Z"/></symbol>
        </defs>
    </svg>

    <div class="preloader-wrapper"><div class="preloader"></div></div>

    <div class="cart-sidebar cart-offcanvas" id="offcanvasCart">
        <div class="cart-offcanvas__header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-cart-fill" style="font-size:1.2rem;color:#1a2235;"></i>
                <span class="cart-offcanvas__title">Mon Panier</span>
            </div>
            <button type="button" class="cart-offcanvas__close" onclick="fermerPanierMobile()" aria-label="Fermer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="offcanvas-body cart-offcanvas__body">
            <ul class="cart-item-list"></ul>

            <div class="cart-offcanvas__footer">
                <div class="mb-3">
                    <input type="text"  id="clientNom"    class="form-control mb-2" placeholder="Votre nom" required>
                    <input type="text"  id="clientPrenom" class="form-control mb-2" placeholder="Votre prénom" required>
                    <input type="email" id="clientEmail"  class="form-control mb-3" placeholder="Votre email">
                </div>

                <div class="cart-total-row">
                    <span class="cart-total-row__label">Total</span>
                    <span class="cart-total-row__value">0.00 €</span>
                </div>

                <button class="cart-pay-btn w-100" id="btn-valider-panier" onclick="validerPanier()">
                    <i class="bi bi-check-circle-fill me-2"></i>Payer par carte
                </button>

                <div class="text-center my-2">ou</div>

                <button class="cart-pay-btn w-100" onclick="validerPanierComptoir()">
                    <i class="bi bi-shop me-2"></i>Payer au comptoir
                </button>
            </div>
        </div>
    </div>

    <header>
        <div class="container-fluid">
            <div class="row py-3 border-bottom align-items-center">
                <div class="col d-flex align-items-center gap-2 header-cat-row-mobile header-cat-row">
                    <a href="#" class="header-cat-item" onclick="chargerProduits(''); return false;">Tout</a>
                    <a href="#" class="header-cat-item" onclick="chargerProduits(1); return false;">Boisson</a>
                    <a href="#" class="header-cat-item" onclick="chargerProduits(2); return false;">Petite faim</a>
                    <a href="#" class="header-cat-item" onclick="chargerProduits(3); return false;">Snacks</a>
                    <a href="#" class="header-cat-item" onclick="chargerProduits(4); return false;">Gourmandise</a>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div id="liste-produits" class="product-grid row row-cols-3 mt-4">
                        <p class="text-center w-100">Chargement des produits...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="cart-mobile-overlay" onclick="fermerPanierMobile()"></div>

    <nav class="mobile-bottom-nav">
        <a href="pageClient.php" class="mobile-bottom-nav__item active" title="Accueil">
            <i class="bi bi-house-door-fill"></i>
        </a>
        <button class="mobile-bottom-nav__item" onclick="ouvrirPanier()" title="Panier"
            style="background:none;border:none;cursor:pointer;position:relative;">
            <i class="bi bi-cart-fill"></i>
            <span class="cart-count-badge" style="display:none;position:absolute;top:4px;right:10px;
                font-size:9px;min-width:15px;height:15px;line-height:15px;text-align:center;
                background:#e74c3c;color:#fff;border-radius:8px;padding:0 2px;">0</span>
        </button>
    </nav>

    <style>
        #liste-produits { display:flex !important; flex-direction:column !important; gap:10px; }
        #liste-produits .col { flex:none !important; max-width:100% !important; width:100% !important; padding:0 !important; }
        .pc-produit { display:flex; align-items:center; gap:12px; background:#fff; border-radius:12px; padding:10px; box-shadow:0 2px 8px rgba(0,0,0,0.07); }
        .pc-produit__img { width:80px; height:80px; object-fit:cover; border-radius:8px; flex-shrink:0; background:#f0f1f4; }
        .pc-produit__info { flex:1; display:flex; flex-direction:column; gap:3px; }
        .pc-produit__nom { font-size:14px; font-weight:600; color:#1a2235; }
        .pc-produit__prix { font-size:15px; font-weight:700; color:#e67e22; }
        .pc-produit__btn { width:100%; background:#1a2235; color:#fff; border:none; border-radius:4px; padding:8px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px; margin-top:4px; }
        .pc-produit__btn:disabled { opacity:0.45; cursor:not-allowed; }
        .pc-produit__btn:not(:disabled):active { background:#FFC43F; color:#1a2235; }
    </style>

    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/panier.js"></script>

    <script>
        window.chargerProduits = function(categorie = '') {
            const conteneur = document.getElementById('liste-produits');
            if (!conteneur) return;
            conteneur.innerHTML = '<p class="text-center w-100">Chargement en cours...</p>';
            let url = 'api/getAllproduit.php';
            if (categorie !== '') url += '?categorie=' + encodeURIComponent(categorie);
            fetch(url)
                .then(r => r.json())
                .then(produits => {
                    if (!produits.length) {
                        conteneur.innerHTML = '<p class="text-center w-100">Aucun produit dans cette catégorie.</p>';
                        return;
                    }
                    conteneur.innerHTML = produits.map(p => {
                        const rupture = parseInt(p.quantiteStock) <= 0;
                        return `
                            <div class="col">
                                <div class="pc-produit">
                                    <img src="${p.image}" alt="${p.nomProduit}" class="pc-produit__img" onerror="this.style.background='#e0e0e0'">
                                    <div class="pc-produit__info">
                                        <span class="pc-produit__nom">${p.nomProduit}</span>
                                        <span class="pc-produit__prix">${parseFloat(p.Prix).toFixed(2)} €</span>
                                        <button class="btn-add-cart pc-produit__btn"
                                            data-id="${p.idproduit}"
                                            data-nom="${p.nomProduit.replace(/"/g,'&quot;')}"
                                            data-prix="${p.Prix}"
                                            data-image="${p.image}"
                                            onclick="ajouterAuPanier(${p.idproduit})"
                                            ${rupture ? 'disabled' : ''}>
                                            <i class="bi bi-plus-lg"></i>
                                            ${rupture ? 'Rupture de stock' : 'Ajouter au panier'}
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                    }).join('');
                });
        };
        chargerProduits('');
    </script>
</body>
</html>