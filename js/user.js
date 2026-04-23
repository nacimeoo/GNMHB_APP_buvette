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
                                <button class="btn stock-btn-edit btn-sm btn-modifier-user"
                                    data-id="${user.id}"
                                    data-prenom="${user.prenom}"
                                    data-nom="${user.nom}"  
                                    data-email="${user.email}"
                                    data-role="${user.role}">
                                <i class="bi bi-pencil-fill"></i></button>
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
            document.querySelectorAll('.btn-modifier-user').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('edit-user-id').value  = btn.dataset.id;
                    document.getElementById('edit-prenom').value   = btn.dataset.prenom;
                    document.getElementById('edit-nom').value      = btn.dataset.nom;
                    document.getElementById('edit-email').value    = btn.dataset.email;
                    document.getElementById('edit-role').value     = btn.dataset.role;

                    new bootstrap.Modal(document.getElementById('modalModifierUser')).show();
                });
            });
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

    document.getElementById('btn-sauvegarder-user').addEventListener('click', () => {
        const formData = new FormData();
        formData.append('id',     document.getElementById('edit-user-id').value);
        formData.append('prenom', document.getElementById('edit-prenom').value);
        formData.append('nom',    document.getElementById('edit-nom').value);
        formData.append('email',  document.getElementById('edit-email').value);
        formData.append('role',   document.getElementById('edit-role').value);

        fetch('api/updateUser.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalModifierUser')).hide();
                window.chargerUtilisateurs();
            } else {
                alert('Erreur : ' + data.message);
            }
        });
    });
});