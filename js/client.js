window.ouvrirCategorie = function(id, nom) {
  document.getElementById('vue-categories').style.display = 'none';
  document.getElementById('vue-produits').style.display   = 'block';
  document.getElementById('titre-categorie').textContent  = nom;

  document.getElementById('btn-home').classList.remove('active');

  chargerProduits(id);

  window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.retourCategories = function() {
  document.getElementById('vue-produits').style.display   = 'none';
  document.getElementById('vue-categories').style.display = 'block';
  document.getElementById('liste-produits').innerHTML     = '';
  document.getElementById('sous-cat-chips').innerHTML     = '';

  document.getElementById('btn-home').classList.add('active');

  window.scrollTo({ top: 0, behavior: 'smooth' });
};


window.filtrerSousCategorie = function(nom, chipEl) {
  document.querySelectorAll('.subcat-chip').forEach(c => c.classList.remove('active'));
  if (chipEl) chipEl.classList.add('active');

  document.querySelectorAll('.subcat-section').forEach(section => {
    section.style.display = (!nom || section.dataset.subcat === nom) ? 'block' : 'none';
  });
};


window.chargerProduits = function(categorie) {
  const conteneur   = document.getElementById('liste-produits');
  const chipWrapper = document.getElementById('sous-cat-chips');
  if (!conteneur) return;

  conteneur.innerHTML = '<p style="text-align:center;padding:40px 0;color:#aaa;font-size:.95rem;">Chargement...</p>';
  if (chipWrapper) chipWrapper.innerHTML = '';

  let url = 'api/getAllproduit.php';
  if (categorie !== '' && categorie != null) {
    url += '?categorie=' + encodeURIComponent(categorie);
  }

  fetch(url)
    .then(r => r.json())
    .then(produits => {
      conteneur.innerHTML = '';

      if (!produits || produits.length === 0) {
        conteneur.innerHTML = '<p style="text-align:center;padding:40px 0;color:#aaa;">Aucun produit dans cette catégorie.</p>';
        return;
      }

      const groupes = {};
      produits.forEach(p => {
        const sc = p.nomSousCategorie || 'Autre';
        if (!groupes[sc]) groupes[sc] = [];
        groupes[sc].push(p);
      });

      const souscats = Object.keys(groupes);

      if (chipWrapper) {
        const toutChip = document.createElement('button');
        toutChip.className   = 'subcat-chip active';
        toutChip.textContent = 'Tout';
        toutChip.onclick = () => filtrerSousCategorie('', toutChip);
        chipWrapper.appendChild(toutChip);

        souscats.forEach(sc => {
          const chip = document.createElement('button');
          chip.className   = 'subcat-chip';
          chip.textContent = sc;
          chip.onclick = () => filtrerSousCategorie(sc, chip);
          chipWrapper.appendChild(chip);
        });
      }

      souscats.forEach(sc => {
        const section = document.createElement('div');
        section.className      = 'subcat-section';
        section.dataset.subcat = sc;

        const titre = document.createElement('h4');
        titre.className   = 'subcat-section-title';
        titre.textContent = sc;
        section.appendChild(titre);

        groupes[sc].forEach(p => {
          const imagePath = p.image || '';
          const prixStr   = parseFloat(p.Prix).toFixed(2);

          const item = document.createElement('div');
          item.className = 'product-list-item';
          item.innerHTML = `
            <img
              src="${imagePath}"
              alt="${p.nomProduit}"
              class="product-list-img"
              onerror="this.style.opacity='.3';"
            >
            <div class="product-list-info">
              <p class="product-list-name">${p.nomProduit}</p>
              <span class="product-list-price">${prixStr}&nbsp;€</span>
              <button
                class="btn-add-panier btn-add-cart"
                data-id="${p.idproduit}"
                data-nom="${p.nomProduit.replace(/"/g, '&quot;')}"
                data-prix="${p.Prix}"
                data-image="${imagePath}"
                onclick="handleAjout(this, ${p.idproduit})"
              >
                <svg width="15" height="15"><use xlink:href="#cart"></use></svg>
                Ajouter
              </button>
            </div>
          `;
          section.appendChild(item);
        });

        conteneur.appendChild(section);
      });
    })
    .catch(err => {
      console.error('Erreur chargement produits :', err);
      conteneur.innerHTML = '<p style="text-align:center;padding:40px 0;color:#c0392b;">Impossible de charger les produits.</p>';
    });
};


window.handleAjout = function(btn, id) {
  ajouterAuPanier(id);
  btn.classList.add('added');
  btn.innerHTML = '<i class="bi bi-check-lg"></i> Ajouté';
  setTimeout(() => {
    btn.classList.remove('added');
    btn.innerHTML = '<svg width="15" height="15"><use xlink:href="#cart"></use></svg> Ajouter';
  }, 1400);
};
