document.addEventListener('DOMContentLoaded', function() {
    const usersTableBody = document.getElementById('users-table-body');
    const addUserBtn = document.getElementById('add-user-btn');
    const userModal = document.getElementById('user-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const userForm = document.getElementById('user-form');
    const modalTitle = document.getElementById('modal-title');

    // Load users on page load
    loadUsers();

    // Event listeners
    addUserBtn.addEventListener('click', () => {
        openModal();
    });

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside modal content
    userModal.addEventListener('click', (event) => {
        if (event.target === userModal) {
            closeModal();
        }
    });

    userForm.addEventListener('submit', handleFormSubmit);

    // Functions
    async function loadUsers() {
        try {
            const response = await fetch('actions/get_users.php');
            const data = await response.json();
            if (data.success) {
                displayUsers(data.users);
            } else {
                alert('Erreur lors du chargement des utilisateurs: ' + data.message);
            }
        } catch (error) {
            alert('Erreur réseau: ' + error.message);
        }
    }

    function displayUsers(users) {
        usersTableBody.innerHTML = '';
        users.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td>${user.secteur_name || ''}</td>
                <td>${user.is_active == 1 ? 'Oui' : 'Non'}</td>
                <td>
                    <button class="btn-icon view-btn" title="Voir" onclick="viewUser(${user.id})"><i class="fas fa-eye"></i></button>
                    <button class="btn-icon edit-btn" title="Modifier" onclick="editUser(${user.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon delete-btn" title="Supprimer" onclick="deleteUser(${user.id})"><i class="fas fa-trash"></i></button>
                </td>
            `;
            usersTableBody.appendChild(tr);
        });
    }

    function openModal() {
        modalTitle.textContent = 'Ajouter un utilisateur';
        userForm.reset();
        document.getElementById('user-id').value = '';
        userModal.style.display = 'block';
    }

    function closeModal() {
        userModal.style.display = 'none';
    }

    async function handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(userForm);
        const id = formData.get('id');
        let url;
        if (id) {
            // Update user
            url = 'actions/update_user.php';
            // Remove password field if empty to avoid sending empty password
            if (!formData.get('password')) {
                formData.delete('password');
            }
        } else {
            // Save new user
            url = 'actions/save_user.php';
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                alert(data.message);
                closeModal();
                loadUsers();
            } else {
                alert('Erreur: ' + data.message);
            }
        } catch (error) {
            alert('Erreur réseau: ' + error.message);
        }
    }

    window.editUser = async function(id) {
        try {
            const response = await fetch(`actions/get_user.php?id=${id}`);
            const data = await response.json();
            if (data.success) {
                modalTitle.textContent = 'Modifier un utilisateur';
                const userIdElem = document.getElementById('user-id');
                const userNameElem = document.getElementById('user-name');
                const userEmailElem = document.getElementById('user-email');
                const userRoleElem = document.getElementById('user-role');
                const userSecteurElem = document.getElementById('user-secteur');
                const userActiveElem = document.getElementById('user-active');
                const userGlobalJuryElem = document.getElementById('user-global-jury');
                // Password field removed from form, so no user-password element

                if (userIdElem) userIdElem.value = data.user.id;
                if (userNameElem) userNameElem.value = data.user.name;
                if (userEmailElem) userEmailElem.value = data.user.email;
                if (userRoleElem) userRoleElem.value = data.user.role;
                if (userSecteurElem) userSecteurElem.value = data.user.secteur_id || '';
                if (userActiveElem) userActiveElem.value = data.user.is_active;
                if (userGlobalJuryElem) userGlobalJuryElem.value = data.user.is_global_jury;

                userModal.style.display = 'block';
            } else {
                alert('Erreur: ' + data.message);
            }
        } catch (error) {
            alert('Erreur réseau: ' + error.message);
        }
    };

    window.deleteUser = async function(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return;
        try {
            const response = await fetch('actions/delete_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            });
            const data = await response.json();
            if (data.success) {
                alert(data.message);
                loadUsers();
            } else {
                alert('Erreur: ' + data.message);
            }
        } catch (error) {
            alert('Erreur réseau: ' + error.message);
        }
    };

    // Load secteurs for the secteur select
    async function loadSecteurs() {
        try {
            const response = await fetch('actions/get_sectors.php');
            const data = await response.json();
            if (data.success) {
                const secteurSelect = document.getElementById('user-secteur');
                secteurSelect.innerHTML = '<option value="">Aucun</option>';
                data.sectors.forEach(secteur => {
                    const option = document.createElement('option');
                    option.value = secteur.id;
                    option.textContent = secteur.nom;
                    secteurSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Erreur chargement secteurs:', error);
        }
    }

    loadSecteurs();
});
