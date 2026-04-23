function calculerTotalEncaisse() {
    fetch('api/getTotalEncaisse.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalEncaisse').textContent = data.totalEncaisse ? data.totalEncaisse + ' €' : '0 €';
        })
        .catch(error => {
            console.error('Erreur Fetch :', error);
            document.getElementById('totalEncaisse').textContent = '0 €';
        });
}

function calculerTotalImpayes() {
    fetch('api/getTotalImpayes.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalImpayes').textContent = data.totalImpayes ? data.totalImpayes + ' €' : '0 €';
        })
        .catch(error => {
            console.error('Erreur Fetch :', error);
            document.getElementById('totalImpayes').textContent = '0 €';
        }); 
}

function calculerTotalCB() {
    fetch('api/getTotalCB.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalCB').textContent = data.totalCB ? data.totalCB + ' €' : '0 €';
        })
        .catch(error => {
            console.error('Erreur Fetch :', error);
            document.getElementById('totalCB').textContent = '0 €';
        });
}

function calculerTotalEspece() {
    fetch('api/getTotalEspece.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalEspece').textContent = data.totalEspece ? data.totalEspece + ' €' : '0 €';
        })
        .catch(error => {
            console.error('Erreur Fetch :', error);
            document.getElementById('totalEspece').textContent = '0 €';
        }); 
}

function calculerTotalCommande() {
    fetch('api/getTotalCommande.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalCommande').textContent = data.totalCommande ? data.totalCommande + ' commandes' : '0 commandes';
        })
        .catch(error => {
            console.error('Erreur Fetch :', error);
            document.getElementById('totalCommande').textContent = '0 commandes';
        });
}



window.chargerCommandes = function() {

    const checkboxFiltre = document.getElementById('filtreImpayes');
    const paramFiltre = (checkboxFiltre && checkboxFiltre.checked) ? '?impaye=1' : '';


    fetch('api/getVente.php' + paramFiltre)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('transactionsTableBody');
            tbody.innerHTML = '';
            data.commandes.forEach(commande => {

                let clientInfo = '';
                if (commande.nom_client && commande.prenom_client) {
                    clientInfo = `${commande.prenom_client} ${commande.nom_client}`;
                } else {
                    clientInfo = 'N/A';
                }


                const tr = document.createElement('tr');

                const estPaye = (commande.etatPaiement == 1);

                const checkboxHTML = estPaye 
                    ? `<input type="checkbox" disabled title="Commande déjà réglée" style="cursor: not-allowed; opacity: 0.5;">` 
                    : `<input type="checkbox" class="ligne-checkbox" value="${commande.idCommande || commande.numTicket}" data-montant="${commande.Montant}">`;

                tr.innerHTML = `
                    <td>
                        ${checkboxHTML}
                    </td>
                    <td>${commande.date}</td>
                    <td>${commande.numTicket}</td>                    
                    <td>${clientInfo}</td>

                    <td>${commande.Montant} €</td>
                    <td>${commande.modePAIEMENT}</td>
                    <td>
                        ${commande.etatPaiement == 1 
                            ? '<span class="stock-status stock-status--bon">Payé</span>' 
                            : '<span class="stock-status stock-status--mauvais">Impayé</span>'}
                    </td>
                    
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Erreur :', error);
            const tbody = document.getElementById('transactionsTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Impossible de charger les commandes.</td></tr>';
        });
    
    
};

document.addEventListener('DOMContentLoaded', function() {
    calculerTotalEncaisse();
    calculerTotalImpayes();
    calculerTotalCB();
    calculerTotalEspece();
    calculerTotalCommande();  
    chargerCommandes();
});

function rafraichirStats() {
    calculerTotalEncaisse();
    calculerTotalImpayes();
    calculerTotalCB();
    calculerTotalEspece();
    calculerTotalCommande();  
    chargerCommandes();
}

window.cocherToutesLesLignes = function(sourceCheckbox) {
    const checkboxes = document.querySelectorAll('.ligne-checkbox:not([disabled])');    
    checkboxes.forEach(checkbox => {
        checkbox.checked = sourceCheckbox.checked;
    });
};



window.ouvrirModalPaiement = function() {
    const checkboxes = document.querySelectorAll('.ligne-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert("Veuillez cocher au moins une commande à régler.");
        return;
    }
    let total = 0;
    checkboxes.forEach(cb => {
        total += parseFloat(cb.dataset.montant || 0);
    });


    document.getElementById('countCommandesSelectionnees').textContent = checkboxes.length;

    document.getElementById('montantTotalSelectionne').textContent = total.toFixed(2) + ' €';
    
    const modalPaiement = new bootstrap.Modal(document.getElementById('modalPaiementGroupe'));
    modalPaiement.show();
};

window.validerPaiementGroupe = function(methodePaiement) {
    const checkboxes = document.querySelectorAll('.ligne-checkbox:checked');
    const idsCommandes = Array.from(checkboxes).map(cb => cb.value);

    fetch('api/FinaliserCommandes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            ids: idsCommandes, 
            methode: methodePaiement 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modalEl = document.getElementById('modalPaiementGroupe');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            modalInstance.hide();
            
            const selectAllBtn = document.getElementById('selectAll');
            if (selectAllBtn) selectAllBtn.checked = false;

            alert(data.message); 
            
            rafraichirStats();
        } else {
            alert("Erreur : " + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur Fetch :', error);
        alert("Erreur de communication avec le serveur.");
    });
};
