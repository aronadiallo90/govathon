document.addEventListener('DOMContentLoaded', function() {
    // Données fictives pour les secteurs
    const sectors = [
        {
            id: 1,
            name: 'Smart City',
            description: 'Solutions innovantes pour les villes intelligentes',
            projectsCount: 5,
            juryCount: 2,
            status: 'active'
        },
        {
            id: 2,
            name: 'Santé',
            description: 'Innovations dans le domaine de la santé',
            projectsCount: 4,
            juryCount: 3,
            status: 'active'
        },
        {
            id: 3,
            name: 'Éducation',
            description: 'Technologies éducatives et apprentissage',
            projectsCount: 3,
            juryCount: 2,
            status: 'active'
        },
        {
            id: 4,
            name: 'Environnement',
            description: 'Solutions pour la protection de l\'environnement',
            projectsCount: 6,
            juryCount: 2,
            status: 'active'
        },
        {
            id: 5,
            name: 'Transport',
            description: 'Innovations dans les transports',
            projectsCount: 3,
            juryCount: 1,
            status: 'active'
        },
        {
            id: 6,
            name: 'Culture',
            description: 'Technologies pour la promotion culturelle',
            projectsCount: 3,
            juryCount: 2,
            status: 'active'
        }
    ];

    // Éléments du DOM
    const sectorsTableBody = document.getElementById('sectors-table-body');
    const addSectorBtn = document.getElementById('add-sector-btn');
    const sectorModal = document.getElementById('sector-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const sectorForm = document.getElementById('sector-form');
    const filterStatus = document.getElementById('filter-status');

    // Fonction pour afficher les secteurs dans le tableau
    function displaySectors(sectorsToDisplay) {
        sectorsTableBody.innerHTML = '';
        sectorsToDisplay.forEach(sector => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sector.id}</td>
                <td>${sector.name}</td>
                <td>${sector.description}</td>
                <td>${sector.projectsCount}</td>
                <td>${sector.juryCount}</td>
                <td>
                    <span class="status-badge ${sector.status}">
                        ${sector.status === 'active' ? 'Actif' : 'Inactif'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon edit-btn" data-id="${sector.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete-btn" data-id="${sector.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            sectorsTableBody.appendChild(row);
        });
    }

    // Afficher les secteurs au chargement
    displaySectors(sectors);

    // Gestion du modal
    function openModal(sector = null) {
        const modalTitle = sectorModal.querySelector('.modal-header h3');
        const form = sectorModal.querySelector('form');
        
        if (sector) {
            modalTitle.textContent = 'Modifier le secteur';
            form.querySelector('#sector-name').value = sector.name;
            form.querySelector('#sector-description').value = sector.description;
            form.querySelector('#sector-status').value = sector.status;
            form.dataset.sectorId = sector.id;
        } else {
            modalTitle.textContent = 'Ajouter un secteur';
            form.reset();
            delete form.dataset.sectorId;
        }
        
        sectorModal.classList.add('show');
    }

    function closeModal() {
        sectorModal.classList.remove('show');
        sectorForm.reset();
    }

    // Événements pour le modal
    addSectorBtn.addEventListener('click', () => openModal());
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Gestion du formulaire
    sectorForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: this.querySelector('#sector-name').value,
            description: this.querySelector('#sector-description').value,
            status: this.querySelector('#sector-status').value
        };

        if (this.dataset.sectorId) {
            // Modification d'un secteur existant
            const index = sectors.findIndex(s => s.id === parseInt(this.dataset.sectorId));
            if (index !== -1) {
                sectors[index] = { ...sectors[index], ...formData };
            }
        } else {
            // Ajout d'un nouveau secteur
            const newSector = {
                id: sectors.length + 1,
                ...formData,
                projectsCount: 0,
                juryCount: 0
            };
            sectors.push(newSector);
        }

        displaySectors(sectors);
        closeModal();
        showNotification('Secteur sauvegardé avec succès !');
    });

    // Gestion des actions (édition/suppression)
    sectorsTableBody.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;

        const sectorId = parseInt(target.dataset.id);
        const sector = sectors.find(s => s.id === sectorId);

        if (target.classList.contains('edit-btn')) {
            openModal(sector);
        } else if (target.classList.contains('delete-btn')) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce secteur ?')) {
                const index = sectors.findIndex(s => s.id === sectorId);
                if (index !== -1) {
                    sectors.splice(index, 1);
                    displaySectors(sectors);
                    showNotification('Secteur supprimé avec succès !');
                }
            }
        }
    });

    // Gestion du filtre
    filterStatus.addEventListener('change', function() {
        const status = this.value;
        const filteredSectors = status === 'all' 
            ? sectors 
            : sectors.filter(sector => sector.status === status);
        displaySectors(filteredSectors);
    });

    // Fonction pour afficher les notifications
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Gestion du menu toggle
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });
}); 