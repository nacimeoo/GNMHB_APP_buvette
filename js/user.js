window.chargerUtilisateurs = function() {
    const tbodyActifs = document.getElementById('table-users-actifs');
    const tbodyAttente = document.getElementById('table-users-attente');

    tbodyActifs.innerHTML = '<tr><td colspan="6" class="text-center">Chargement...</td></tr>';
    tbodyAttente.innerHTML = '<tr><td colspan="5" class="text-center">Chargement...</td></tr>';

    fetch('api/getUsers.php')
        .then(response => response.json())
        .then(users => {
            tbodyActifs.innerHTML = '';
            tbodyAttente.innerHTML = '';

            let countAttente = 0;

            users.forEach(user => {
                const nomComplet = user.prenom + ' ' + user.nom;

                if (user.statut === 'valide') {
                    tbodyActifs.innerHTML += `
                        <tr>
                            <td class="fw-bold">${nomComplet}</td>
                            <td><span class="badge bg-light text-dark border">${user.role}</span></td>
                            <td>${user.email}</td>
                            <td>2024-03-01</td> <td><span class="stock-status stock-status--bon">Actif</span></td>
                            <td>
                                <button class="btn stock-btn-edit btn-sm"><i class="bi bi-pencil-fill"></i></button>
                                <button class="btn stock-btn-delete btn-sm"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                    `;
                } else if (user.statut === 'en_attente') {
                    countAttente++;
                    tbodyAttente.innerHTML += `
                        <tr>
                            <td class="fw-bold">${nomComplet}</td>
                            <td><span class="badge bg-light text-dark border">${user.role}</span></td>
                            <td>${user.email}</td>
                            <td>2024-03-01</td>
                            <td>
                                <button onclick="validerUtilisateur(${user.id})" class="btn btn-sm btn-success">Valider</button>
                            </td>
                        </tr>
                    `;
                }
            });

            if (countAttente === 0) {
                tbodyAttente.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun utilisateur en attente.</td></tr>';
            }
        })
        .catch(error => console.error('Erreur:', error));
};

window.validerUtilisateur = function(id) {
    if (confirm("Voulez-vous autoriser cet utilisateur à accéder à l'application ?")) {
        const formData = new FormData();
        formData.append('id', id);

        fetch('api/validerUser.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.chargerUtilisateurs();
            } else {
                alert("Erreur : " + data.message);
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.chargerUtilisateurs();
});