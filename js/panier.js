let panier = [];

window.ajouterAuPanier = function(idproduit) {
    const qtyInput = document.getElementById('qty-' + idproduit);
    const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;

    const btn = document.querySelector('.btn-add-cart[data-id="' + idproduit + '"]');
    const nom   = btn ? btn.dataset.nom   : 'Produit';
    const prix  = btn ? parseFloat(btn.dataset.prix) : 0;
    const image = btn ? btn.dataset.image : '';

    const existant = panier.find(p => p.idproduit == idproduit);
    if (existant) {
        existant.quantite += qty;
    } else {
        panier.push({ idproduit, nom, prix, image, quantite: qty });
    }

    mettreAJourAffichagePanier();
    ouvrirPanier();
};

window.modifierQuantitePanier = function(idproduit, delta) {
    const item = panier.find(p => p.idproduit == idproduit);
    if (!item) return;
    item.quantite += delta;
    if (item.quantite <= 0) panier = panier.filter(p => p.idproduit != idproduit);
    mettreAJourAffichagePanier();
};

window.supprimerDuPanier = function(idproduit) {
    panier = panier.filter(p => p.idproduit != idproduit);
    mettreAJourAffichagePanier();
};

window.viderPanier = function() {
    panier = [];
    mettreAJourAffichagePanier();
};

function mettreAJourAffichagePanier() {
    const liste   = document.querySelector('.cart-item-list');
    const totalEl = document.querySelector('.cart-total-row__value');
    const badges  = document.querySelectorAll('.cart-count-badge');

    if (!liste) return;

    liste.innerHTML = '';
    let totalPrix = 0;
    let totalQty  = 0;

    if (panier.length === 0) {
        liste.innerHTML = '<li class="cart-empty-msg"><i class="bi bi-cart-x"></i><span>Votre panier est vide</span></li>';
    }

    panier.forEach(item => {
        totalPrix += item.prix * item.quantite;
        totalQty  += item.quantite;

        const li = document.createElement('li');
        li.className = 'cart-item';
        li.innerHTML = `
            <img src="${item.image}" alt="${item.nom}" class="cart-item__img">
            <div class="cart-item__info">
                <span class="cart-item__name">${item.nom}</span>
                <div class="cart-item__qty-controls">
                    <button class="cart-qty-btn" onclick="modifierQuantitePanier(${item.idproduit}, -1)">
                        <svg width="12" height="12"><use xlink:href="#minus"></use></svg>
                    </button>
                    <span class="cart-qty-value">${item.quantite}</span>
                    <button class="cart-qty-btn" onclick="modifierQuantitePanier(${item.idproduit}, 1)">
                        <svg width="12" height="12"><use xlink:href="#plus"></use></svg>
                    </button>
                </div>
                <span class="cart-item__price">${(item.prix * item.quantite).toFixed(2)} €</span>
            </div>
            <button class="cart-item__delete" onclick="supprimerDuPanier(${item.idproduit})" title="Supprimer">
                <svg width="15" height="15"><use xlink:href="#trash"></use></svg>
            </button>
        `;
        liste.appendChild(li);
    });

    if (totalEl) totalEl.textContent = totalPrix.toFixed(2) + ' €';
    badges.forEach(badge => {
        badge.textContent = totalQty;
        badge.style.display = totalQty > 0 ? 'flex' : 'none';
    });
}

function ouvrirPanier() {
    if (window.innerWidth <= 767) {
        document.getElementById('offcanvasCart').classList.add('cart-mobile-open');
        const overlay = document.getElementById('cart-mobile-overlay');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

window.fermerPanierMobile = function() {
    document.getElementById('offcanvasCart').classList.remove('cart-mobile-open');
    const overlay = document.getElementById('cart-mobile-overlay');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
};


window.validerPanier = function() {
    if (panier.length === 0) return alert("Votre panier est vide !");

    const inputNom    = document.getElementById('clientNom');
    const inputPrenom = document.getElementById('clientPrenom');
    const inputEmail  = document.getElementById('clientEmail');

    const nomClient    = inputNom    ? inputNom.value.trim()    : '';
    const prenomClient = inputPrenom ? inputPrenom.value.trim() : '';
    const emailClient  = inputEmail  ? inputEmail.value.trim()  : '';

    if (nomClient === '' || prenomClient === '') {
        return alert("Veuillez renseigner au moins le nom et le prénom du client.");
    }

    const btnPay = document.getElementById('btn-valider-panier');
    if (btnPay) {
        btnPay.innerHTML  = '<i class="bi bi-hourglass-split me-2"></i>Validation en cours...';
        btnPay.disabled   = true;
    }

    const dataPanier = {
        items:          panier,
        nom_client:     nomClient,
        prenom_client:  prenomClient,
        email_client:   emailClient
    };

    fetch('api/payerSimule.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(dataPanier)
    })
    .then(r => r.json())
    .then(data => {
        if (btnPay) {
            btnPay.disabled  = false;
            btnPay.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Valider la commande';
        }

        if (data.success) {
            panier = [];
            mettreAJourAffichagePanier();

            if(inputNom) inputNom.value = '';
            if(inputPrenom) inputPrenom.value = '';
            if(inputEmail) inputEmail.value = '';

            afficherConfirmationPaiement(data.numTicket, data.montant);
        } else {
            alert("Erreur lors de l'enregistrement : " + data.message);
        }
    })
    .catch(err => {
        console.error("Erreur Fetch :", err);
        alert("Erreur de connexion avec le serveur.");
        if (btnPay) {
            btnPay.disabled  = false;
            btnPay.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Valider la commande';
        }
    });
};


function afficherConfirmationPaiement(numTicket, montant) {
    const ancien = document.getElementById('modal-confirmation');
    if (ancien) ancien.remove();

    const overlay = document.createElement('div');
    overlay.id = 'modal-confirmation';
    overlay.style.cssText = `
        position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,.55); backdrop-filter:blur(4px);
        display:flex; align-items:center; justify-content:center; padding:20px;
    `;
    overlay.innerHTML = `
        <div style="background:#fff; border-radius:24px; padding:40px 32px;
            max-width:360px; width:100%; text-align:center;
            box-shadow:0 20px 60px rgba(0,0,0,.25);
            animation:popIn .3s cubic-bezier(.34,1.56,.64,1);">
            <div style="width:72px;height:72px;border-radius:50%;background:#e8f5e9;
                display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="bi bi-check-lg" style="font-size:2.2rem;color:#27ae60;"></i>
            </div>
            <h2 style="font-size:1.4rem;font-weight:800;color:#1a2235;margin:0 0 8px;">Commande validée !</h2>
            <p style="color:#888;font-size:.95rem;margin:0 0 20px;">Votre commande a bien été enregistrée.</p>
            <p style="color:#888;font-size:.95rem;margin:0 0 20px;">Votre ticket vous a été envoyé par email.</p>
            <div style="background:#f4f5f7;border-radius:14px;padding:16px 20px;margin-bottom:28px;text-align:left;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="color:#888;font-size:.85rem;">Ticket</span>
                    <span style="font-weight:800;color:#1a2235;">${numTicket}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;font-size:.85rem;">Total payé</span>
                    <span style="font-weight:800;color:#c0392b;">${montant} €</span>
                </div>
            </div>
            <button onclick="document.getElementById('modal-confirmation').remove()" style="
                width:100%;background:#1a2235;color:#fff;border:none;
                border-radius:12px;padding:14px;font-size:.95rem;
                font-weight:800;cursor:pointer;font-family:inherit;">Fermer</button>
        </div>
        <style>
            @keyframes popIn { from{transform:scale(.8);opacity:0} to{transform:scale(1);opacity:1} }
        </style>
    `;
    document.body.appendChild(overlay);
}


window.validerPanierComptoir = function() {
    if (panier.length === 0) return alert("Votre panier est vide.");

    const inputNom    = document.getElementById('clientNom');
    const inputPrenom = document.getElementById('clientPrenom');
    const inputEmail  = document.getElementById('clientEmail');

    const nomClient    = inputNom    ? inputNom.value.trim()    : '';
    const prenomClient = inputPrenom ? inputPrenom.value.trim() : '';
    const emailClient  = inputEmail  ? inputEmail.value.trim()  : '';

    if (nomClient === '' || prenomClient === '') {
        return alert("Veuillez renseigner votre nom et prénom.");
    }

    const boutonsPay  = document.querySelectorAll('.cart-pay-btn');
    const btnComptoir = boutonsPay.length > 1 ? boutonsPay[1] : null;
    if (btnComptoir) btnComptoir.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Traitement...';

    fetch('api/validerPanierComptoire.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
            items:         panier,
            type_paiement: 'comptoir',
            nom_client:    nomClient,
            prenom_client: prenomClient,
            email_client:  emailClient
        })
    })
    .then(r => r.json())
    .then(data => {
        if (btnComptoir) btnComptoir.innerHTML = '<i class="bi bi-shop me-2"></i>Payer au comptoir';

        if (data.success) {
            panier = [];
            mettreAJourAffichagePanier();
            fermerPanierMobile();

            if (inputNom)    inputNom.value    = '';
            if (inputPrenom) inputPrenom.value = '';
            if (inputEmail)  inputEmail.value  = '';

            afficherConfirmationComptoir(data.numTicket || '-', data.montant || 'À régler au comptoir');
        } else {
            alert("Erreur lors de la commande : " + data.message);
        }
    })
    .catch(err => {
        console.error("Erreur d'envoi :", err);
        if (btnComptoir) btnComptoir.innerHTML = '<i class="bi bi-shop me-2"></i>Payer au comptoir';
        alert("Erreur de communication avec le serveur.");
    });
};

function afficherConfirmationComptoir(numTicket, montant) {
    const ancien = document.getElementById('modal-confirmation');
    if (ancien) ancien.remove();

    const overlay = document.createElement('div');
    overlay.id = 'modal-confirmation';
    overlay.style.cssText = `
        position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,.55); backdrop-filter:blur(4px);
        display:flex; align-items:center; justify-content:center; padding:20px;
    `;
    overlay.innerHTML = `
        <div style="background:#fff; border-radius:24px; padding:40px 32px;
            max-width:360px; width:100%; text-align:center;
            box-shadow:0 20px 60px rgba(0,0,0,.25);
            animation:popIn .3s cubic-bezier(.34,1.56,.64,1);">
            <div style="width:72px;height:72px;border-radius:50%;background:#e8f5e9;
                display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="bi bi-check-lg" style="font-size:2.2rem;color:#27ae60;"></i>
            </div>
            <h2 style="font-size:1.4rem;font-weight:800;color:#1a2235;margin:0 0 8px;">Commande enregistrée !</h2>
            <p style="color:#888;font-size:.95rem;margin:0 0 20px;">Rendez-vous au comptoir pour finaliser le paiement.</p>
            <div style="background:#f4f5f7;border-radius:14px;padding:16px 20px;margin-bottom:28px;text-align:left;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="color:#888;font-size:.85rem;">Ticket</span>
                    <span style="font-weight:800;color:#1a2235;">${numTicket}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;font-size:.85rem;">Montant</span>
                    <span style="font-weight:800;color:#c0392b;">${montant} €</span>
                </div>
            </div>
            <button onclick="document.getElementById('modal-confirmation').remove()" style="
                width:100%;background:#1a2235;color:#fff;border:none;
                border-radius:12px;padding:14px;font-size:.95rem;
                font-weight:800;cursor:pointer;font-family:inherit;">Fermer</button>
        </div>
        <style>
            @keyframes popIn { from{transform:scale(.8);opacity:0} to{transform:scale(1);opacity:1} }
        </style>
    `;
    document.body.appendChild(overlay);
}