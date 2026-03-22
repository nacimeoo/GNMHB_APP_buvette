window.chargerStock = function() {
    const conteneurStock = document.getElementById('stock-produits');
    const totalStock = document.getElementById('total-stock-valeur');
    const valeurStock = document.getElementById('valeur-stock-valeur');
    const stockBon = document.getElementById('stock-bon-valeur');
    const stockMauvais = document.getElementById('stock-bas-valeur');
    const stockCritique = document.getElementById('stock-critique-valeur');

    if (conteneurStock) {
        conteneurStock.innerHTML = '<p class="text-center w-100">Chargement en cours...</p>';

        fetch('api/getProduit.php')
            .then(response => response.json())
            .then(produits => {
                conteneurStock.innerHTML = '';
                window.produitCharge = produits;

                if (stockBon)      stockBon.textContent      = calculerStockBon();
                if (stockMauvais)  stockMauvais.textContent  = calculerStockMauvais();
                if (stockCritique) stockCritique.textContent = calculerStockCritique();
                if (valeurStock)   valeurStock.textContent   = calculerValeurStock().toFixed(2) + ' €';
                if (totalStock)    totalStock.textContent    = produits.length;

                if (produits.length === 0) {
                    conteneurStock.innerHTML = '<p class="text-center w-100">Aucun produit en stock.</p>';
                    return;
                }

                produits.forEach(produit => {
                    const qte = produit.quantite;
                    const statutHTML = qte > 40
                        ? '<span class="stock-status stock-status--bon">En stock</span>'
                        : qte > 10
                            ? '<span class="stock-status stock-status--bas">Mauvais</span>'
                            : '<span class="stock-status stock-status--critique">Critique</span>';

                    const datePerem = produit.datePeremption ?? 'N/A';

                    const nomEscaped = produit.nomProduit.replace(/'/g, "\\'");
                    const imageEscaped = (produit.image ?? '').replace(/'/g, "\\'");

                    const stockHTML = `
                        <tr>
                            <td><img src="${produit.image ?? ''}" alt="${produit.nomProduit}" style="width: 50px; height: auto;"></td>
                            <td>${produit.nomProduit}</td>
                            <td>${produit.nomCategorie}</td>
                            <td>${produit.Prix} €</td>
                            <td>${qte}</td>
                            <td>${datePerem}</td>
                            <td>${statutHTML}</td>
                            <td>
                                <button
                                    onclick="ouvrirModalModif(${produit.idproduit}, '${nomEscaped}', ${produit.idSousCategorie}, ${produit.Prix}, ${qte}, '${imageEscaped}')"
                                    class="btn stock-btn-edit btn-sm">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button
                                    onclick="supprimerProduit(${produit.idproduit})"
                                    class="btn stock-btn-delete btn-sm">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    conteneurStock.innerHTML += stockHTML;
                });
            })
            .catch(error => {
                console.error('Erreur :', error);
                conteneurStock.innerHTML = '<p class="text-danger w-100 text-center">Impossible de charger le stock.</p>';
            });
    }
};

window.supprimerProduit = function(id) {
    if (confirm("Voulez-vous vraiment supprimer ce produit de la base de données ?")) {
        const formData = new FormData();
        formData.append('id', id);

        fetch('api/deleteProduit.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.chargerStock();
                } else {
                    alert("Erreur lors de la suppression : " + data.message);
                }
            })
            .catch(error => console.error("Erreur:", error));
    }
};


window.ouvrirModalModif = function(id, nom, idSousCategorie, prix, quantite, image_url) {
    document.getElementById('editIdProduit').value         = id;
    document.getElementById('editNomProduit').value        = nom;
    document.getElementById('editCategorieProduit').value  = idSousCategorie;
    document.getElementById('editPrixProduit').value       = prix;
    document.getElementById('editQuantiteProduit').value   = quantite;
    document.getElementById('editImageProduit').value      = image_url;

    const modalEdit = new bootstrap.Modal(document.getElementById('modalEditProduit'));
    modalEdit.show();
};

document.addEventListener('DOMContentLoaded', () => {
    window.chargerStock();

    // ── Autocomplete ──────────────────────────────────────────────
    const inputNom      = document.getElementById('nomProduit');
    const listAuto      = document.getElementById('autocomplete-list');
    const cacheId       = document.getElementById('idProduitCache');
    const infoConnu     = document.getElementById('info-produit-connu');
    const infoNom       = document.getElementById('info-produit-nom');
    const infoPrix      = document.getElementById('info-produit-prix');
    const infoCat       = document.getElementById('info-produit-cat');
    const sectionNouv   = document.getElementById('section-nouveau-produit');

    let debounceTimer = null;

    function resetFormulaire() {
        cacheId.value = '';
        infoConnu.classList.add('d-none');
        sectionNouv.classList.add('d-none');
        listAuto.style.display = 'none';
        listAuto.innerHTML = '';
    }

    function selectionnerProduit(p) {
        inputNom.value      = p.nomProduit;
        cacheId.value       = p.idproduit;
        infoNom.textContent = p.nomProduit;
        infoPrix.textContent = parseFloat(p.Prix).toFixed(2) + ' €';
        infoCat.textContent  = p.nomSousCategorie;
        infoConnu.classList.remove('d-none');
        sectionNouv.classList.add('d-none');
        listAuto.style.display = 'none';
        listAuto.innerHTML = '';
    }

    function afficherNouveauProduit() {
        cacheId.value = '';
        infoConnu.classList.add('d-none');
        sectionNouv.classList.remove('d-none');
        listAuto.style.display = 'none';
        listAuto.innerHTML = '';
    }

    if (inputNom) {
        inputNom.addEventListener('input', () => {
            const q = inputNom.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < 1) {
                resetFormulaire();
                return;
            }

            // Si l'utilisateur retape après avoir sélectionné un produit, on réinitialise
            if (cacheId.value) {
                cacheId.value = '';
                infoConnu.classList.add('d-none');
                sectionNouv.classList.add('d-none');
            }

            debounceTimer = setTimeout(() => {
                fetch('api/searchProduit.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(resultats => {
                        listAuto.innerHTML = '';

                        if (resultats.length === 0) {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action text-warning fw-semibold';
                            li.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Nouveau produit : <em>' + q + '</em>';
                            li.addEventListener('click', () => afficherNouveauProduit());
                            listAuto.appendChild(li);
                        } else {
                            resultats.forEach(p => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.innerHTML = '<span class="fw-semibold">' + p.nomProduit + '</span>'
                                    + '<span class="text-muted ms-2" style="font-size:.8rem;">' + p.nomSousCategorie + ' — ' + parseFloat(p.Prix).toFixed(2) + ' €</span>';
                                li.addEventListener('click', () => selectionnerProduit(p));
                                listAuto.appendChild(li);
                            });

                            // Option pour forcer la création d'un nouveau produit
                            const liNouv = document.createElement('li');
                            liNouv.className = 'list-group-item list-group-item-action text-dark';
                            liNouv.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Ajouter "<em>' + q + '</em>" comme nouveau produit';
                            liNouv.addEventListener('click', () => afficherNouveauProduit());
                            listAuto.appendChild(liNouv);
                        }

                        listAuto.style.display = 'block';
                    });
            }, 250);
        });

        // Fermer la liste si clic ailleurs
        document.addEventListener('click', (e) => {
            if (!inputNom.contains(e.target) && !listAuto.contains(e.target)) {
                listAuto.style.display = 'none';
            }
        });
    }

    // Réinitialiser le formulaire à l'ouverture du modal
    const modalEl = document.getElementById('modalAjoutProduit');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', () => {
            document.getElementById('form-ajout-produit').reset();
            resetFormulaire();
        });
    }

    // ── Sauvegarde ────────────────────────────────────────────────
    const btnSauvegarder = document.getElementById('btn-sauvegarder-produit');
    if (btnSauvegarder) {
        btnSauvegarder.addEventListener('click', () => {
            const idProduit      = document.getElementById('idProduitCache').value;
            const nom            = document.getElementById('nomProduit').value.trim();
            const datePeremption = document.getElementById('datePeremption').value;
            const quantite       = document.getElementById('quantiteProduit').value;
            const estNouv        = !sectionNouv.classList.contains('d-none');

            if (!nom || !datePeremption || !quantite) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            // Produit inconnu : valider les champs supplémentaires
            if (!idProduit) {
                if (!estNouv) {
                    alert('Veuillez sélectionner un produit dans la liste ou créer un nouveau produit.');
                    return;
                }
                const categorie = document.getElementById('categorieProduit').value;
                const prix      = document.getElementById('prixProduit').value;
                if (!categorie || !prix) {
                    alert('Veuillez remplir la sous-catégorie et le prix pour le nouveau produit.');
                    return;
                }
            }

            const formData = new FormData();
            formData.append('nomProduit',     nom);
            formData.append('idProduit',      idProduit);
            formData.append('datePeremption', datePeremption);
            formData.append('quantite',       quantite);

            if (!idProduit) {
                formData.append('idSousCategorie', document.getElementById('categorieProduit').value);
                formData.append('typeCategorie',   document.getElementById('typeProduit').value);
                formData.append('prix',            document.getElementById('prixProduit').value);
                formData.append('seuil_alerte',    document.getElementById('seuilProduit').value || 0);
                formData.append('image',           document.getElementById('imageProduit').value || '');
            }

            fetch('api/addStock.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalAjoutProduit')).hide();
                        window.chargerStock();
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur Fetch :', error);
                    alert('Impossible de joindre le serveur.');
                });
        });
    }

    const formEdit = document.getElementById('form-edit-produit');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('idproduit', document.getElementById('editIdProduit').value);
            formData.append('nomProduit', document.getElementById('editNomProduit').value);
            formData.append('idSousCategorie', document.getElementById('editCategorieProduit').value);
            formData.append('prix', document.getElementById('editPrixProduit').value);
            formData.append('image', document.getElementById('editImageProduit').value);

            formData.append('quantite', document.getElementById('editQuantiteProduit').value);
            
            formData.append('typeCategorie', 'Simple'); 
            formData.append('seuil_alerte', 10);

            fetch('api/updateProduit.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditProduit')).hide();
                        window.chargerStock(); 
                    } else {
                        alert('Erreur lors de la modification : ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur Fetch :', error);
                });
        });
    }
});


function calculerValeurStock() {
    let total = 0;
    if (window.produitCharge) {
        window.produitCharge.forEach(p => {
            total += parseFloat(p.Prix) * parseInt(p.quantite, 10);  
        });
    }
    return total;
}

function calculerStockBon() {
    return (window.produitCharge || []).filter(p => p.quantite > 40).length;
}

function calculerStockMauvais() {
    return (window.produitCharge || []).filter(p => p.quantite <= 40 && p.quantite > 10).length;
}

function calculerStockCritique() {
    return (window.produitCharge || []).filter(p => p.quantite <= 10).length;
}