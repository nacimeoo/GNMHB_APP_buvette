/* ============================================================
   MOUVEMENTS DE STOCK — JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    // ── Données globales chargées au démarrage ──────────────────
    let entrepots      = [];
    let produitsList   = [];
    let sousCategories = [];
    let compteurLigne  = 0;

    // ── Init ────────────────────────────────────────────────────
    chargerDonnees();
    document.getElementById('entree-date').valueAsDate = new Date();
    ajouterLigne();   // première ligne par défaut

    // ── Chargement des données de référence ─────────────────────
    async function chargerDonnees() {
        try {
            const [resEnt, resProd, resSC] = await Promise.all([
                fetch('api/getEntrepots.php').then(r => r.json()),
                fetch('api/getProduitsList.php').then(r => r.json()),
                fetch('api/getSousCategories.php').then(r => r.json()),
            ]);
            entrepots      = resEnt;
            produitsList   = resProd;
            sousCategories = resSC;
            remplirSelects();
        } catch (e) {
            console.error('Erreur chargement données :', e);
        }
        chargerHistorique();
    }

    function remplirSelects() {
        // Entrepôts
        const selEntreeEnt = document.getElementById('entree-entrepot');
        const selPerteEnt  = document.getElementById('perte-entrepot');
        entrepots.forEach(e => {
            const opt = `<option value="${e.idEntrepot}">${e.nom}</option>`;
            selEntreeEnt.insertAdjacentHTML('beforeend', opt);
            selPerteEnt.insertAdjacentHTML('beforeend', opt);
        });

        // Produits (perte)
        const selPerteProd = document.getElementById('perte-produit');
        produitsList.forEach(p => {
            selPerteProd.insertAdjacentHTML('beforeend',
                `<option value="${p.idproduit}">${p.nomProduit}</option>`);
        });
    }

    // ── SECTION 1 — ENTRÉE DE STOCK ─────────────────────────────

    document.getElementById('btn-ajouter-ligne').addEventListener('click', ajouterLigne);
    document.getElementById('btn-valider-entree').addEventListener('click', validerEntree);

    function ajouterLigne() {
        const idx = compteurLigne++;
        const conteneur = document.getElementById('entree-lignes');

        const div = document.createElement('div');
        div.className = 'ligne-produit';
        div.dataset.idx = idx;
        div.innerHTML = `
            <div class="row g-2 align-items-start">

              <!-- Produit -->
              <div class="col-12 col-md">
                <label class="lbl lbl-req d-md-none">Produit</label>
                <div class="position-relative">
                  <input type="text"   class="form-control input-recherche"  placeholder="Tapez pour rechercher…" autocomplete="off">
                  <input type="hidden" class="input-id-produit">
                  <ul class="list-group dropdown-auto"></ul>
                </div>
                <div class="info-produit-connu d-none mt-1">
                  <i class="bi bi-check-circle-fill text-success me-1"></i>
                  <span class="fw-semibold info-nom"></span>
                  <span class="text-muted ms-2 info-cat" style="font-size:.8rem;"></span>
                </div>
                <!-- Panneau nouveau produit (caché par défaut) -->
                <div class="nouveau-produit-panel d-none">
                  <div class="panel-title"><i class="bi bi-plus-circle me-1"></i>Nouveau produit</div>
                  <div class="row g-2">
                    <div class="col-sm-7">
                      <label class="lbl lbl-req" style="font-size:.78rem;">Sous-catégorie</label>
                      <select class="form-select form-select-sm input-sc">
                        <option value="">— Sélectionner —</option>
                        ${sousCategories.map(sc =>
                          `<option value="${sc.idSousCategorie}">${sc.nomCategorie} › ${sc.nomSousCategorie}</option>`
                        ).join('')}
                      </select>
                    </div>
                    <div class="col-sm-5">
                      <label class="lbl lbl-req" style="font-size:.78rem;">Prix de vente (€)</label>
                      <input type="number" min="0" step="0.01" class="form-control form-control-sm input-prix-produit" placeholder="2.50">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Quantité -->
              <div class="col-5 col-md-auto" style="min-width:100px;">
                <label class="lbl lbl-req d-md-none">Quantité</label>
                <input type="number" min="1" value="1" class="form-control input-quantite" placeholder="1">
              </div>

              <!-- Date de péremption -->
              <div class="col-7 col-md-auto" style="min-width:160px;">
                <label class="lbl lbl-req d-md-none">Date de péremption</label>
                <input type="date" class="form-control input-date-peremption">
              </div>

              <!-- Supprimer -->
              <div class="col-auto d-flex align-items-start pt-1">
                <button type="button" class="btn btn-sm btn-outline-danger btn-suppr" title="Supprimer cette ligne" style="margin-top:2px;">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
        `;

        conteneur.appendChild(div);
        attacherAutocomplete(div);
        div.querySelector('.btn-suppr').addEventListener('click', () => {
            div.remove();
            mettreAJourSuppr();
        });
        mettreAJourSuppr();
    }

    function mettreAJourSuppr() {
        const lignes = document.querySelectorAll('#entree-lignes .ligne-produit');
        lignes.forEach(l => {
            l.querySelector('.btn-suppr').disabled = lignes.length <= 1;
        });
    }

    // ── Autocomplete ────────────────────────────────────────────
    function attacherAutocomplete(ligne) {
        const input     = ligne.querySelector('.input-recherche');
        const listEl    = ligne.querySelector('.dropdown-auto');
        const cacheId   = ligne.querySelector('.input-id-produit');
        const infoConnu = ligne.querySelector('.info-produit-connu');
        const infoNom   = ligne.querySelector('.info-nom');
        const infoCat   = ligne.querySelector('.info-cat');
        const panelNouv = ligne.querySelector('.nouveau-produit-panel');

        let timer = null;

        function reset() {
            cacheId.value = '';
            infoConnu.classList.add('d-none');
            panelNouv.classList.add('d-none');
            listEl.style.display = 'none';
            listEl.innerHTML = '';
        }

        function selectionnerProduit(p) {
            input.value         = p.nomProduit;
            cacheId.value       = p.idproduit;
            infoNom.textContent = p.nomProduit;
            infoCat.textContent = p.nomSousCategorie ?? '';
            infoConnu.classList.remove('d-none');
            panelNouv.classList.add('d-none');
            listEl.style.display = 'none';
            listEl.innerHTML = '';
        }

        function activerNouveauProduit() {
            cacheId.value = '';
            infoConnu.classList.add('d-none');
            panelNouv.classList.remove('d-none');
            listEl.style.display = 'none';
            listEl.innerHTML = '';
        }

        input.addEventListener('input', () => {
            const q = input.value.trim();
            clearTimeout(timer);

            if (q.length < 1) { reset(); return; }

            // Si un produit était sélectionné, le désélectionner
            if (cacheId.value) {
                cacheId.value = '';
                infoConnu.classList.add('d-none');
                panelNouv.classList.add('d-none');
            }

            timer = setTimeout(() => {
                fetch('api/searchProduit.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(resultats => {
                        listEl.innerHTML = '';

                        resultats.forEach(p => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action';
                            li.innerHTML = `<span class="fw-semibold">${p.nomProduit}</span>
                                <span class="text-muted ms-2" style="font-size:.8rem;">${p.nomSousCategorie ?? ''} — ${parseFloat(p.Prix).toFixed(2)} €</span>`;
                            li.addEventListener('click', () => selectionnerProduit(p));
                            listEl.appendChild(li);
                        });

                        // Option "Créer nouveau produit"
                        const liNouv = document.createElement('li');
                        liNouv.className = 'list-group-item item-nouveau';
                        liNouv.innerHTML = `<i class="bi bi-plus-circle me-2"></i>Créer nouveau produit : <em>"${q}"</em>`;
                        liNouv.addEventListener('click', () => activerNouveauProduit());
                        listEl.appendChild(liNouv);

                        listEl.style.display = 'block';
                    })
                    .catch(() => {});
            }, 230);
        });

        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !listEl.contains(e.target)) {
                listEl.style.display = 'none';
            }
        });
    }

    // ── Validation et soumission de l'entrée ────────────────────
    async function validerEntree() {
        const idEntrepot = document.getElementById('entree-entrepot').value;
        const date       = document.getElementById('entree-date').value;
        const montant    = document.getElementById('entree-montant').value;

        if (!idEntrepot) { afficherToast('Veuillez sélectionner un entrepôt.', 'error'); return; }
        if (!date)        { afficherToast('Veuillez renseigner la date.', 'error'); return; }

        const lignesDOM = document.querySelectorAll('#entree-lignes .ligne-produit');
        const lignes = [];

        for (const l of lignesDOM) {
            const recherche  = l.querySelector('.input-recherche').value.trim();
            const idProduit  = l.querySelector('.input-id-produit').value;
            const quantite   = parseInt(l.querySelector('.input-quantite').value);
            const datePer    = l.querySelector('.input-date-peremption').value || null;
            const panelNouv  = l.querySelector('.nouveau-produit-panel');
            const estNouv    = !panelNouv.classList.contains('d-none');

            if (!recherche) {
                afficherToast('Une ligne de produit est vide.', 'error'); return;
            }
            if (!quantite || quantite < 1) {
                afficherToast(`Quantité invalide pour "${recherche}".`, 'error'); return;
            }

            const ligne = { nomProduit: recherche, quantite, datePeremption: datePer };

            if (idProduit) {
                ligne.idProduit = parseInt(idProduit);
            } else if (estNouv) {
                const idSC = l.querySelector('.input-sc').value;
                const prix = parseFloat(l.querySelector('.input-prix-produit').value);
                if (!idSC || !prix || prix <= 0) {
                    afficherToast(`Sous-catégorie et prix obligatoires pour "${recherche}".`, 'error'); return;
                }
                ligne.idSousCategorie = parseInt(idSC);
                ligne.prix = prix;
            } else {
                afficherToast(`Sélectionnez "${recherche}" dans la liste ou choisissez "Créer nouveau produit".`, 'error'); return;
            }

            lignes.push(ligne);
        }

        const btn = document.getElementById('btn-valider-entree');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';

        try {
            const res  = await fetch('api/entreeStock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idEntrepot: parseInt(idEntrepot), date, montantTotal: montant || null, lignes }),
            });
            const data = await res.json();

            if (data.success) {
                afficherToast('Entrée de stock enregistrée avec succès !', 'success');
                reinitialiserFormEntree();
                chargerHistorique();
            } else {
                afficherToast('Erreur : ' + data.message, 'error');
            }
        } catch (e) {
            afficherToast('Erreur de connexion avec le serveur.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Valider l\'entrée';
        }
    }

    function reinitialiserFormEntree() {
        document.getElementById('entree-entrepot').value = '';
        document.getElementById('entree-date').valueAsDate = new Date();
        document.getElementById('entree-montant').value = '';
        document.getElementById('entree-lignes').innerHTML = '';
        compteurLigne = 0;
        ajouterLigne();
    }

    // ── SECTION 2 — DÉCLARER UNE PERTE ─────────────────────────

    document.getElementById('btn-valider-perte').addEventListener('click', validerPerte);

    async function validerPerte() {
        const idProduit  = document.getElementById('perte-produit').value;
        const idEntrepot = document.getElementById('perte-entrepot').value;
        const quantite   = parseInt(document.getElementById('perte-quantite').value);

        if (!idProduit)              { afficherToast('Veuillez sélectionner un produit.', 'error'); return; }
        if (!idEntrepot)             { afficherToast('Veuillez sélectionner un entrepôt.', 'error'); return; }
        if (!quantite || quantite < 1) { afficherToast('Quantité invalide.', 'error'); return; }

        const btn = document.getElementById('btn-valider-perte');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';

        try {
            const res  = await fetch('api/declarerPerte.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idProduit: parseInt(idProduit), idEntrepot: parseInt(idEntrepot), quantite }),
            });
            const data = await res.json();

            if (data.success) {
                afficherToast('Perte déclarée avec succès.', 'success');
                document.getElementById('form-perte').reset();
                chargerHistorique();
            } else {
                afficherToast('Erreur : ' + data.message, 'error');
            }
        } catch (e) {
            afficherToast('Erreur de connexion avec le serveur.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Déclarer la perte';
        }
    }

    // ── SECTION 3 — HISTORIQUE ───────────────────────────────────

    document.getElementById('btn-refresh-historique').addEventListener('click', chargerHistorique);

    async function chargerHistorique() {
        const tbody = document.getElementById('historique-body');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Chargement…</td></tr>';

        try {
            const mouvements = await fetch('api/getHistorique.php').then(r => r.json());

            if (!mouvements.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Aucun mouvement enregistré.</td></tr>';
                return;
            }

            tbody.innerHTML = mouvements.map(m => {
                const date = new Date(m.dateMouvement).toLocaleString('fr-FR', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
                const badge = `<span class="badge-mvt badge-${m.typeMouvement}">${m.typeMouvement}</span>`;
                const iconeQte = m.typeMouvement === 'ENTREE'
                    ? `<span class="text-success fw-semibold">+${m.quantite}</span>`
                    : `<span class="text-danger fw-semibold">-${m.quantite}</span>`;

                return `<tr>
                    <td style="white-space:nowrap;font-size:.82rem;color:#6b7490;">${date}</td>
                    <td>${badge}</td>
                    <td class="fw-semibold">${m.nomProduit}</td>
                    <td>${iconeQte}</td>
                    <td style="font-size:.85rem;color:#555;">${m.nomEntrepot}</td>
                    <td style="font-size:.82rem;color:#6b7490;">${m.operateur}</td>
                </tr>`;
            }).join('');

        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Impossible de charger l\'historique.</td></tr>';
        }
    }

    // ── Toast ────────────────────────────────────────────────────
    let toastTimer = null;
    function afficherToast(message, type = 'success') {
        const toast = document.getElementById('mvt-toast');
        toast.className = `mvt-toast mvt-toast--${type}`;
        toast.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'} me-2"></i>${message}`;
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 4500);
    }

});
