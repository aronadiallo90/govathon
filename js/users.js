document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, initialisation des événements');
    
    const usersTableBody = document.getElementById('users-table-body');
    const addUserBtn = document.getElementById('add-user-btn');
    const userModal = document.getElementById('user-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const userForm = document.getElementById('user-form');
    const modalTitle = document.getElementById('modal-title');

    // État de chargement
    let isLoading = false;

    // Fonction pour afficher/masquer le chargement
    function setLoading(loading) {
        console.log('Changement de l\'état de chargement:', loading);
        isLoading = loading;
        const submitBtn = userForm.querySelector('button[type="submit"]');
        if (loading) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enregistrer';
        }
    }

    // Fonction pour afficher une notification
    function showNotification(message, type = 'success') {
        console.log('Notification:', message, type);
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Load users on page load
    loadUsers();

    // Event listeners
    addUserBtn.addEventListener('click', () => {
        console.log('Clic sur le bouton d\'ajout');
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
            console.log('Chargement des utilisateurs');
            const response = await fetch('actions/get_users.php');
            const data = await response.json();
            if (data.success) {
                displayUsers(data.users);
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            showNotification('Erreur lors du chargement des utilisateurs', 'error');
        }
    }

    function displayUsers(users) {
        console.log('Affichage des utilisateurs:', users);
        usersTableBody.innerHTML = '';
        users.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td>${user.secteur_name || ''}</td>
                <td>
                    <span class="status-badge ${user.is_active == 1 ? 'active' : 'inactive'}">
                        ${user.is_active == 1 ? 'Actif' : 'Inactif'}
                    </span>
                </td>
                <td>
                    <button class="btn-icon view-btn" title="Voir" onclick="viewUser(${user.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit-btn" title="Modifier" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-btn" title="Supprimer" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            usersTableBody.appendChild(tr);
        });
    }

    // Fonction pour ouvrir le modal
    function openModal() {
        console.log('Ouverture du modal');
        modalTitle.textContent = 'Ajouter un utilisateur';
        userForm.reset();
        document.getElementById('user-id').value = '';
        
        // Pour un nouvel utilisateur, tous les champs sont requis
        document.getElementById('user-name').required = true;
        document.getElementById('user-email').required = true;
        document.getElementById('user-role').required = true;
        document.getElementById('user-password').required = true;
        
        // Afficher le modal
        userModal.style.display = 'flex';
        userModal.classList.add('show');
    }

    // Fonction pour fermer le modal
    function closeModal() {
        console.log('Fermeture du modal');
        userModal.classList.remove('show');
        setTimeout(() => {
        userModal.style.display = 'none';
        }, 300);
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        if (isLoading) return;

        let requestData; // Utiliser let au lieu de const pour les données à envoyer
        const formData = new FormData(userForm);
        const userId = formData.get('id');
        
        try {
            setLoading(true);
            
            // Création d'un nouvel utilisateur
            if (!userId) {
                requestData = {
                    name: formData.get('name'),
                    email: formData.get('email'),
                    role: formData.get('role'),
                    password: formData.get('password'),
                    secteur_id: formData.get('secteur_id') || null,
                    is_active: formData.get('is_active') || '0',
                    is_global_jury: formData.get('is_global_jury') || '0'
                };

                // Validation pour la création
                if (!requestData.name || !requestData.email || !requestData.role || !requestData.password) {
                    showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                    return;
                }
            } 
            // Modification d'un utilisateur existant
            else {
                requestData = {
                    id: userId,
                    role: formData.get('role')
                };

                // Ajouter uniquement les champs modifiés
                const name = formData.get('name');
                if (name) requestData.name = name;

                const email = formData.get('email');
                if (email) requestData.email = email;

                const password = formData.get('password');
                if (password) requestData.password = password;

                const secteur_id = formData.get('secteur_id');
                requestData.secteur_id = secteur_id || null;

                const is_active = formData.get('is_active');
                if (is_active) requestData.is_active = is_active;

                const is_global_jury = formData.get('is_global_jury');
                if (is_global_jury) requestData.is_global_jury = is_global_jury;
            }

            const response = await fetch('actions/save_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();
            if (data.success) {
                showNotification(data.message || 'Opération réussie');
                closeModal();
                await loadUsers();
            } else {
                showNotification(data.message || 'Une erreur est survenue', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Une erreur est survenue', 'error');
        } finally {
            setLoading(false);
        }
    }

    // Fonction utilitaire pour valider l'email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Fonction pour éditer un utilisateur
    window.editUser = async function(id) {
        console.log('Édition de l\'utilisateur:', id);
        if (isLoading) return;

        try {
            setLoading(true);
            const response = await fetch(`actions/get_user.php?id=${id}`);
            const data = await response.json();
            console.log('Données de l\'utilisateur reçues:', data);

            if (data.success && data.user) {
                modalTitle.textContent = 'Modifier un utilisateur';
                const user = data.user;
                
                // Remplir le formulaire avec les données de l'utilisateur
                document.getElementById('user-id').value = user.id;
                document.getElementById('user-name').value = user.name;
                document.getElementById('user-email').value = user.email;
                document.getElementById('user-role').value = user.role;
                document.getElementById('user-secteur').value = user.secteur_id || '';
                document.getElementById('user-active').value = user.is_active;
                document.getElementById('user-global-jury').value = user.is_global_jury;
                
                // Pour la modification, aucun champ n'est requis
                document.getElementById('user-name').required = false;
                document.getElementById('user-email').required = false;
                document.getElementById('user-role').required = false;
                document.getElementById('user-password').required = false;
                document.getElementById('user-password').value = '';

                // Afficher le modal
                userModal.style.display = 'flex';
                userModal.classList.add('show');
            } else {
                showNotification(data.message || 'Erreur lors de la récupération des données', 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des données:', error);
            showNotification('Une erreur est survenue lors de la récupération des données', 'error');
        } finally {
            setLoading(false);
        }
    };

    window.deleteUser = async function(id) {
        console.log('Suppression de l\'utilisateur:', id);
        if (isLoading) return;

        if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return;

        try {
            setLoading(true);
            const response = await fetch('actions/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });

            const data = await response.json();
            console.log('Réponse de suppression:', data);

            if (data.success) {
                showNotification(data.message);
                await loadUsers();
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Une erreur est survenue lors de la suppression', 'error');
        } finally {
            setLoading(false);
        }
    };

    // Load secteurs for the secteur select
    async function loadSecteurs() {
        try {
            console.log('Chargement des secteurs');
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
            showNotification('Erreur lors du chargement des secteurs', 'error');
        }
    }

    loadSecteurs();
});
