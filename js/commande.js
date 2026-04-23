let commandesData = [];

document.addEventListener('DOMContentLoaded', () => {
    chargerTicketsCuisine();
    setInterval(chargerTicketsCuisine, 15000);
});

function validerTicket(idCommande) {
    fetch('api/validerCommande.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idCommande: idCommande })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) chargerTicketsCuisine();
    })
    .catch(err => console.error('Erreur validerTicket :', err));
}

function chargerTicketsCuisine() {
    fetch('api/getCommande.php')
        .then(response => response.json())
        .then(data => {
            const colSimples = document.getElementById('col-simples');
            const colComposees = document.getElementById('col-composees');
            const colGrosses = document.getElementById('col-grosses');

            colSimples.innerHTML = '';
            colComposees.innerHTML = '';
            colGrosses.innerHTML = '';

            commandesData = data.commandes || [];

        if (!data.commandes || data.commandes.length === 0) {
                colSimples.innerHTML = '<p class="text-muted small">Aucune commande.</p>';
                colComposees.innerHTML = '<p class="text-muted small">Aucune commande.</p>';
                colGrosses.innerHTML = '<p class="text-muted small">Aucune commande.</p>';
                return;
            }

            data.commandes.forEach(commande => {
                const dateObj = new Date(commande.date);
                const dateAffichee = dateObj.toLocaleDateString('fr-FR', {day:'2-digit', month:'short', year:'numeric'}) + ', ' + dateObj.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});

                let compteurCompose = 0; 

                commande.produits.forEach(p => {
                    if (p.type === 'Composé' || p.type === 'Compose' || p.type === 'Matiere_Premiere') {
                        compteurCompose += parseInt(p.quantite);
                    }
                });

                let estGrosse = (compteurCompose >= 3);
                
                let estComposee = (compteurCompose > 0 && !estGrosse); 
                
                
                const badgePaiement = commande.etatPaiement == 0
                    ? `<span class="badge bg-danger ms-2">Impayé</span>`
                    : `<span class="badge bg-success ms-2">Payé</span>`;

                const bordureCard = commande.etatPaiement == 0 ? 'border-danger' : '';

                let cardHTML = `
                <div class="card border mb-3 rounded-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-0">Ticket #${commande.numTicket}</h6>
                        ${badgePaiement}
                        <small class="text-muted d-block mb-3">${dateAffichee}</small>
                `;

                commande.produits.forEach(produit => {
                    cardHTML += `
                        <div class="d-flex align-items-center mb-3">
                            <img src="${produit.image || 'images/coca.png'}" class="ticket-img me-3 rounded" alt="${produit.nom}" style="height: 50px; width: 50px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-semibold text-truncate" style="max-width: 150px;">${produit.nom}</p>
                                <small class="text-muted">${produit.type}</small>
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="fw-bold">${produit.prix} €</span>
                                    <span class="text-primary fw-bold small">x${produit.quantite}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });

                cardHTML += `
                        <div class="d-flex justify-content-between align-items-end mt-4 pt-3 border-top">
                            <span class="text-muted small">X${commande.totalItems} produits</span>
                            <button class="btn btn-outline-success btn-sm px-3 rounded-3" onclick="validerTicket(${commande.idCommande})">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                `;

                if (estGrosse) {
                    colGrosses.innerHTML += cardHTML;
                } else if (estComposee) {
                    colComposees.innerHTML += cardHTML;
                } else {
                    colSimples.innerHTML += cardHTML;
                }
            });
        })
        .catch(error => console.error('Erreur Fetch :', error));
}



