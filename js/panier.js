
let panier = [];

window.ajouterAuPanier = function(idproduit) {
    const qtyInput = document.getElementById('qty-' + idproduit);
    const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;

    const btn = document.querySelector('.btn-add-cart[data-id="' + idproduit + '"]');
    const nom    = btn ? btn.dataset.nom   : 'Produit';
    const prix   = btn ? parseFloat(btn.dataset.prix) : 0;
    const image  = btn ? btn.dataset.image : '';

    const existant = panier.find(p => p.idproduit == idproduit);
    if (existant) {
        existant.quantite += qty;
    } else {
        panier.push({ idproduit: idproduit, nom: nom, prix: prix, image: image, quantite: qty });
    }

    mettreAJourAffichagePanier();
    ouvrirPanier();
};

window.modifierQuantitePanier = function(idproduit, delta) {
    const item = panier.find(p => p.idproduit == idproduit);
    if (!item) return;
    item.quantite += delta;
    if (item.quantite <= 0) {
        panier = panier.filter(p => p.idproduit != idproduit);
    }
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
    const offcanvasEl = document.getElementById('offcanvasCart');
    if (offcanvasEl && typeof bootstrap !== 'undefined') {
        const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        bsOffcanvas.show();
    }
}

document.addEventListener('click', function(e) {
    const plusBtn  = e.target.closest('.quantity-right-plus');
    const minusBtn = e.target.closest('.quantity-left-minus');

    if (plusBtn) {
        e.preventDefault();
        const group = plusBtn.closest('.product-qty');
        if (group) {
            const input = group.querySelector('input.input-number');
            if (input) input.value = parseInt(input.value || 1) + 1;
        }
    }

    if (minusBtn) {
        e.preventDefault();
        const group = minusBtn.closest('.product-qty');
        if (group) {
            const input = group.querySelector('input.input-number');
            if (input) {
                const val = parseInt(input.value || 1);
                if (val > 1) input.value = val - 1;
            }
        }
    }
});

window.validerPanier = function() {
    if (panier.length === 0) {
        alert("Votre panier est vide !");
        return;
    }

    const btnPay = document.querySelector('.cart-pay-btn');
    if (btnPay) btnPay.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Traitement...';

    fetch('api/payerSimule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items: panier })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Fermer le panneau panier
            const offcanvasEl = document.getElementById('offcanvasCart');
            if (offcanvasEl && typeof bootstrap !== 'undefined') {
                bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).hide();
            }

            // Vider le panier
            panier = [];
            mettreAJourAffichagePanier();

            // Afficher la modale de confirmation
            afficherConfirmationPaiement(data.numTicket, data.montant);

        } else {
            alert("Erreur : " + data.message);
            if (btnPay) btnPay.innerHTML = '<i class="bi bi-credit-card-fill me-2"></i>Payer';
        }
    })
    .catch(err => {
        console.error('Erreur Fetch :', err);
        alert("Erreur de connexion avec le serveur.");
        if (btnPay) btnPay.innerHTML = '<i class="bi bi-credit-card-fill me-2"></i>Payer';
    });
};

function afficherConfirmationPaiement(numTicket, montant) {
    // Supprimer une éventuelle ancienne modale
    const ancien = document.getElementById('modal-confirmation');
    if (ancien) ancien.remove();

    const overlay = document.createElement('div');
    overlay.id = 'modal-confirmation';
    overlay.style.cssText = `
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
    `;

    overlay.innerHTML = `
        <div style="
            background: #fff; border-radius: 24px; padding: 40px 32px;
            max-width: 360px; width: 100%; text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            animation: popIn .3s cubic-bezier(.34,1.56,.64,1);
        ">
            <div style="
                width: 72px; height: 72px; border-radius: 50%;
                background: #e8f5e9; display: flex; align-items: center;
                justify-content: center; margin: 0 auto 20px;
            ">
                <i class="bi bi-check-lg" style="font-size: 2.2rem; color: #27ae60;"></i>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #1a2235; margin: 0 0 8px;">Commande validée !</h2>
            <p style="color: #888; font-size: 0.95rem; margin: 0 0 20px;">Votre paiement a bien été enregistré.</p>

            <div style="
                background: #f4f5f7; border-radius: 14px; padding: 16px 20px;
                margin-bottom: 28px; text-align: left;
            ">
                <div style="display:flex; justify-content:space-between; margin-bottom: 6px;">
                    <span style="color:#888; font-size:.85rem;">Ticket</span>
                    <span style="font-weight: 800; color: #1a2235;">${numTicket}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#888; font-size:.85rem;">Total payé</span>
                    <span style="font-weight: 800; color: #c0392b;">${montant} €</span>
                </div>
            </div>

            <button onclick="document.getElementById('modal-confirmation').remove()" style="
                width: 100%; background: #1a2235; color: #fff;
                border: none; border-radius: 12px; padding: 14px;
                font-size: 0.95rem; font-weight: 800; cursor: pointer;
                font-family: inherit;
            ">Fermer</button>
        </div>
        <style>
            @keyframes popIn {
                from { transform: scale(.8); opacity: 0; }
                to   { transform: scale(1);  opacity: 1; }
            }
        </style>
    `;

    document.body.appendChild(overlay);

    // Réinitialiser le bouton payer
    const btnPay = document.querySelector('.cart-pay-btn');
    if (btnPay) btnPay.innerHTML = '<i class="bi bi-credit-card-fill me-2"></i>Payer';
}
