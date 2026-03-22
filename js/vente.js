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
    fetch('api/getVente.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('transactionsTableBody');
            tbody.innerHTML = '';
            data.commandes.forEach(commande => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${commande.date}</td>
                    <td>${commande.numTicket}</td>
                    <td>${commande.Montant} €</td>
                    <td>${commande.modePAIEMENT}</td>
                    <td>
                        ${commande.etatPaiement == 1 
                            ? '<span class="stock-status stock-status--bon">Payé</span>' 
                            : '<span class="stock-status stock-status--mauvais">Impayé</span>'}
                    </td>
                    
                    <td class="text-end">
                        ${commande.etatPaiement == 1 
                            ? '' 
                            : `<button class="btn btn-sm btn-outline-primary">Encaisser</button> `}
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
