document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const juryTableBody = document.getElementById('jury-table-body');
    const addJuryBtn = document.getElementById('add-jury-btn');
    const juryModal = document.getElementById('jury-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const juryForm = document.getElementById('jury-form');
    const filterRole = document.getElementById('filter-role');
    const filterStatus = document.getElementById('filter-status');
    let currentPage = 1;
    const itemsPerPage = 10;

    // Charger les jurys
    async function loadJurys() {
        try {
            const response = await fetch('actions/get_jurys.php');
            const data = await response.json();
            
            if (data.success) {
                displayJuries(data.jurys);
                updatePagination(data.jurys.length);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    // Fonction pour obtenir le texte du rôle en français
    function getRoleText(isGlobal) {
        return isGlobal ? 'Jury Global' : 'Jury Sectoriel';
    }

    // Fonction pour obtenir le texte du statut en français
    function getStatusText(status) {
        return status ? 'Actif' : 'Inactif';
    }

    // Fonction pour obtenir les initiales
    function getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase();
    }

    // Fonction pour afficher les jurys dans le tableau avec animation
    function displayJuries(juries) {
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageJuries = juries.slice(start, end);

        juryTableBody.innerHTML = '';
        pageJuries.forEach(jury => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${jury.id}</td>
                <td>
                    <div class="jury-profile">
                        <div class="jury-avatar president">${getInitials(jury.name)}</div>
                        <div class="jury-info">
                            <div class="jury-name">${jury.name}</div>
                            <div class="jury-email">${jury.email}</div>
                        </div>
                    </div>
                </td>
                <td><span class="role-badge ${jury.is_global_jury ? 'global' : 'sectorial'}">${getRoleText(jury.is_global_jury)}</span></td>
                <td>${jury.secteur_nom || 'Non assigné'}</td>
                <td>${jury.projets_evalues || 0}</td>
                <td><span class="status-badge ${jury.is_active ? 'active' : 'inactive'}">${getStatusText(jury.is_active)}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon view-btn" onclick="viewJury(${jury.id})" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon edit-btn" onclick="editJury(${jury.id})" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete-btn" onclick="deleteJury(${jury.id})" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            // Animation d'entrée
            row.style.opacity = '0';
            juryTableBody.appendChild(row);
            setTimeout(() => row.style.opacity = '1', 10);
        });
    }

    // Fonction pour afficher une notification avec animation
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Éditer un jury
    window.editJury = async function(id) {
        try {
            const response = await fetch(`actions/get_jury.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const jury = data.jury;
                document.getElementById('jury-id').value = jury.id;
                document.getElementById('jury-name').value = jury.name;
                document.getElementById('jury-email').value = jury.email;
                document.getElementById('jury-password').value = '';
                document.getElementById('jury-secteur').value = jury.secteur_id || '';
                document.getElementById('jury-is-global').checked = jury.is_global_jury;
                document.getElementById('jury-status').value = jury.is_active ? 'active' : 'inactive';
                
                document.querySelector('.modal-header h3').textContent = 'Modifier un jury';
                juryModal.style.display = 'block';
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    };

    // Supprimer un jury
    window.deleteJury = async function(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce jury ?')) return;
        
        try {
            const response = await fetch('actions/delete_jury.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                await loadJurys();
                showNotification('Jury supprimé avec succès');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    };

    // Gestion du formulaire
    juryForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            id: document.getElementById('jury-id').value || null,
            name: document.getElementById('jury-name').value.trim(),
            email: document.getElementById('jury-email').value.trim(),
            password: document.getElementById('jury-password').value,
            secteur_id: document.getElementById('jury-secteur').value || null,
            is_global_jury: document.getElementById('jury-is-global').checked,
            is_active: document.getElementById('jury-status').value === 'active'
        };

        try {
            const response = await fetch('actions/save_jury.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                await loadJurys();
                closeModal();
                showNotification('Jury sauvegardé avec succès');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    });

    // Fonctions utilitaires
    function updatePagination(totalItems) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        document.querySelector('.pagination span').textContent = `Page ${currentPage} sur ${totalPages}`;
        
        // Mise à jour des boutons de pagination
        document.querySelectorAll('.pagination button').forEach(button => {
            if (button.querySelector('.fa-chevron-left')) {
                button.disabled = currentPage === 1;
            } else {
                button.disabled = currentPage === totalPages;
            }
        });
    }

    function closeModal() {
        juryModal.style.display = 'none';
        juryForm.reset();
        document.getElementById('jury-id').value = '';
    }

    // Gestion des filtres
    function applyFilters() {
        const roleFilter = filterRole.value;
        const statusFilter = filterStatus.value;

        loadJurys().then(data => {
            if (data && data.jurys) {
                const filteredJuries = data.jurys.filter(jury => {
                    const roleMatch = roleFilter === 'all' || 
                        (roleFilter === 'global' && jury.is_global_jury) ||
                        (roleFilter === 'sectorial' && !jury.is_global_jury);
                    const statusMatch = statusFilter === 'all' || 
                        (statusFilter === 'active' && jury.is_active) ||
                        (statusFilter === 'inactive' && !jury.is_active);
                    return roleMatch && statusMatch;
                });
                displayJuries(filteredJuries);
            }
        });
    }

    // Event listeners
    if (filterRole) filterRole.addEventListener('change', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);

    // Événements
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Gestion de la pagination
    document.querySelectorAll('.pagination button').forEach(button => {
        button.addEventListener('click', () => {
            if (button.querySelector('.fa-chevron-left')) {
                if (currentPage > 1) currentPage--;
            } else {
                currentPage++;
            }
            loadJurys();
        });
    });

    // Charger les jurys au démarrage avec animation
    loadJurys();
});