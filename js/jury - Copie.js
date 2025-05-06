document.addEventListener('DOMContentLoaded', function() {
    // Données fictives pour les jurys
    const juryData = [
        {
            id: 1,
            name: "Dr. Jean Dupont",
            email: "jean.dupont@example.com",
            role: "president",
            projectsEvaluated: 12,
            status: "active",
            expertise: ["smart-city", "sante"],
            bio: "Expert en innovation urbaine avec plus de 15 ans d'expérience"
        },
        {
            id: 2,
            name: "Marie Martin",
            email: "marie.martin@example.com",
            role: "member",
            projectsEvaluated: 8,
            status: "active",
            expertise: ["education", "culture"],
            bio: "Spécialiste en technologies éducatives"
        },
        {
            id: 3,
            name: "Pierre Durand",
            email: "pierre.durand@example.com",
            role: "expert",
            projectsEvaluated: 15,
            status: "inactive",
            expertise: ["environnement", "transport"],
            bio: "Consultant en mobilité durable"
        }
    ];

    // Éléments du DOM
    const juryTableBody = document.getElementById('jury-table-body');
    const addJuryBtn = document.getElementById('add-jury-btn');
    const juryModal = document.getElementById('jury-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const juryForm = document.getElementById('jury-form');
    const filterRole = document.getElementById('filter-role');
    const filterStatus = document.getElementById('filter-status');

    // Fonction pour obtenir les initiales
    function getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase();
    }

    // Fonction pour obtenir le texte du rôle en français
    function getRoleText(role) {
        const roleMap = {
            'president': 'Président',
            'member': 'Membre',
            'expert': 'Expert'
        };
        return roleMap[role] || role;
    }

    // Fonction pour obtenir le texte du statut en français
    function getStatusText(status) {
        const statusMap = {
            'active': 'Actif',
            'inactive': 'Inactif'
        };
        return statusMap[status] || status;
    }

    // Fonction pour afficher les jurys dans le tableau
    function displayJuries(juries = juryData) {
        juryTableBody.innerHTML = '';
        juries.forEach(jury => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${jury.id}</td>
                <td>
                    <div class="jury-profile">
                        <div class="jury-avatar ${jury.role}">${getInitials(jury.name)}</div>
                        <div class="jury-info">
                            <span class="jury-name">${jury.name}</span>
                            <span class="jury-email">${jury.email}</span>
                        </div>
                    </div>
                </td>
                <td><span class="role-badge ${jury.role}">${getRoleText(jury.role)}</span></td>
                <td>${jury.email}</td>
                <td>${jury.projectsEvaluated}</td>
                <td><span class="status-badge ${jury.status}">${getStatusText(jury.status)}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon view-btn" data-id="${jury.id}" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon edit-btn" data-id="${jury.id}" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete-btn" data-id="${jury.id}" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            juryTableBody.appendChild(row);
        });
    }

    // Fonction pour afficher une notification
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

    // Gestion du modal
    function openModal(jury = null) {
        const modalTitle = juryModal.querySelector('.modal-header h3');
        const form = juryModal.querySelector('form');
        
        if (jury) {
            modalTitle.textContent = 'Modifier le jury';
            form.querySelector('#jury-name').value = jury.name;
            form.querySelector('#jury-email').value = jury.email;
            form.querySelector('#jury-role').value = jury.role;
            form.querySelector('#jury-expertise').value = jury.expertise;
            form.querySelector('#jury-bio').value = jury.bio;
            form.querySelector('#jury-status').value = jury.status;
            form.dataset.juryId = jury.id;
        } else {
            modalTitle.textContent = 'Ajouter un jury';
            form.reset();
            delete form.dataset.juryId;
        }
        
        juryModal.style.display = 'block';
    }

    function closeModal() {
        juryModal.style.display = 'none';
        juryForm.reset();
    }

    // Événements pour le modal
    addJuryBtn.addEventListener('click', () => openModal());
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Gestion du formulaire
    juryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: this.querySelector('#jury-name').value,
            email: this.querySelector('#jury-email').value,
            role: this.querySelector('#jury-role').value,
            expertise: Array.from(this.querySelector('#jury-expertise').selectedOptions).map(option => option.value),
            bio: this.querySelector('#jury-bio').value,
            status: this.querySelector('#jury-status').value,
            projectsEvaluated: 0
        };

        if (this.dataset.juryId) {
            // Modification d'un jury existant
            const index = juryData.findIndex(j => j.id === parseInt(this.dataset.juryId));
            if (index !== -1) {
                juryData[index] = { ...juryData[index], ...formData };
            }
        } else {
            // Ajout d'un nouveau jury
            const newJury = {
                id: juryData.length + 1,
                ...formData
            };
            juryData.push(newJury);
        }

        displayJuries(juryData);
        closeModal();
        showNotification('Jury sauvegardé avec succès !');
    });

    // Gestion des actions (édition/suppression/visualisation)
    juryTableBody.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;

        const juryId = parseInt(target.dataset.id);
        const jury = juryData.find(j => j.id === juryId);

        if (target.classList.contains('edit-btn')) {
            openModal(jury);
        } else if (target.classList.contains('delete-btn')) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce jury ?')) {
                const index = juryData.findIndex(j => j.id === juryId);
                if (index !== -1) {
                    juryData.splice(index, 1);
                    displayJuries(juryData);
                    showNotification('Jury supprimé avec succès !');
                }
            }
        } else if (target.classList.contains('view-btn')) {
            // TODO: Implémenter la visualisation détaillée du jury
            alert('Fonctionnalité de visualisation à implémenter');
        }
    });

    // Gestion des filtres
    function applyFilters() {
        const roleFilter = filterRole.value;
        const statusFilter = filterStatus.value;

        const filteredJuries = juryData.filter(jury => {
            const roleMatch = roleFilter === 'all' || jury.role === roleFilter;
            const statusMatch = statusFilter === 'all' || jury.status === statusFilter;
            return roleMatch && statusMatch;
        });

        displayJuries(filteredJuries);
    }

    filterRole.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);

    // Afficher les jurys au chargement
    displayJuries();
}); 