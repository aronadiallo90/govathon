// Remplacer les données statiques par des appels API
let sectorsData = [];

// Éléments DOM
const searchInput = document.querySelector('.search-bar input');
const sectorsGrid = document.querySelector('.sectors-grid');
const modal = document.getElementById('sectorModal');
const sectorForm = document.getElementById('sectorForm');
const closeModalBtn = document.querySelector('.close-modal');
const cancelBtn = document.getElementById('cancelBtn');
const iconOptions = document.querySelectorAll('.icon-option');
const addSectorBtn = document.getElementById('add-sector-btn');
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');

// État de l'application
let currentEditId = null;
let isEditing = false;
let selectedIcon = 'fa-laptop';

// Fonction pour afficher une notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        ${message}
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Chargement initial des secteurs
async function loadSectors() {
    try {
        const response = await fetch('actions/get_sectors.php');
        const data = await response.json();
        if (data.success) {
            sectorsData = data.sectors;
            displaySectors();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erreur lors du chargement des secteurs', 'error');
    }
}

// Affichage des secteurs
function displaySectors() {
    sectorsGrid.innerHTML = '';
    
    if (sectorsData.length === 0) {
        sectorsGrid.innerHTML = `
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <p>Aucun secteur trouvé</p>
            </div>`;
        return;
    }

    sectorsData.forEach(sector => {
        const card = document.createElement('div');
        card.className = 'sector-card';
        card.setAttribute('data-id', sector.id);
        
        card.innerHTML = `
            <div class="sector-header">
                <i class="fas ${sector.icon || 'fa-building'} sector-icon"></i>
                <h3 class="sector-title">${sector.nom}</h3>
            </div>
            <div class="sector-body">
                <p class="sector-description">${sector.description || 'Aucune description'}</p>
                <div class="action-buttons">
                    <button class="btn-icon edit-btn" onclick="editSector(${sector.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-btn" onclick="deleteSector(${sector.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        sectorsGrid.appendChild(card);
    });
}

// Gestion du modal
function openModal() {
    const modal = document.getElementById('sectorModal');
    if (!modal) {
        console.error('Modal element not found');
        return;
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    if (!isEditing) {
        // Réinitialiser le formulaire
        sectorForm.reset();
        selectedIcon = 'fa-laptop'; // Icône par défaut
        
        // Réinitialiser la sélection des icônes
        const iconOptions = document.querySelectorAll('.icon-option');
        iconOptions.forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.icon === selectedIcon);
        });
    }
}

function closeModal() {
    const modal = document.getElementById('sectorModal');
    if (!modal) {
        console.error('Modal element not found');
        return;
    }
    
    modal.classList.remove('show');
    document.body.style.overflow = '';
    
    // Reset state
    isEditing = false;
    currentEditId = null;
    selectedIcon = 'fa-laptop';
    updateSelectedIcon();
}

// Gestion des icônes
function updateSelectedIcon() {
    iconOptions.forEach(option => {
        const icon = option.querySelector('i').classList[1];
        option.classList.toggle('selected', icon === selectedIcon);
    });
}

iconOptions.forEach(option => {
    option.addEventListener('click', () => {
        selectedIcon = option.querySelector('i').classList[1];
        updateSelectedIcon();
    });
});

// Gestion du formulaire
sectorForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        nom: document.getElementById('sectorName').value.trim(),
        description: document.getElementById('sectorDescription').value.trim(),
        icon: selectedIcon
    };

    if (currentEditId) {
        formData.id = currentEditId;
    }

    try {
        const response = await fetch('actions/save_sector.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        
        if (result.success) {
            await loadSectors();
            closeModal();
            showNotification(isEditing ? 'Secteur modifié avec succès' : 'Secteur ajouté avec succès');
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Erreur lors de la sauvegarde', 'error');
    }
});

// Édition d'un secteur
async function editSector(id) {
    try {
        const response = await fetch(`actions/get_sector.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            currentEditId = id;
            isEditing = true;
            document.querySelector('#sectorModal h3').textContent = 'Modifier le secteur';
            
            document.getElementById('sectorName').value = data.sector.nom;
            document.getElementById('sectorDescription').value = data.sector.description || '';
            selectedIcon = data.sector.icon || 'fa-building';
            updateSelectedIcon();
            
            openModal();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erreur lors du chargement du secteur', 'error');
    }
}

// Suppression d'un secteur
async function deleteSector(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce secteur ?')) {
        try {
            const response = await fetch('actions/delete_sector.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id })
            });

            const result = await response.json();
            
            if (result.success) {
                await loadSectors();
                showNotification('Secteur supprimé avec succès');
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            showNotification('Erreur lors de la suppression', 'error');
        }
    }
}

// Recherche
searchInput.addEventListener('input', debounce(() => {
    const searchTerm = searchInput.value.toLowerCase();
    const filteredSectors = sectorsData.filter(sector => 
        sector.nom.toLowerCase().includes(searchTerm) || 
        sector.description.toLowerCase().includes(searchTerm)
    );
    // Ne pas modifier sectorsData original
    displayFilteredSectors(filteredSectors);
}, 300));

// Nouvelle fonction pour afficher les secteurs filtrés
function displayFilteredSectors(sectors) {
    sectorsGrid.innerHTML = '';
    
    if (sectors.length === 0) {
        sectorsGrid.innerHTML = `
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <p>Aucun secteur trouvé</p>
            </div>`;
        return;
    }

    sectors.forEach(sector => {
        const card = document.createElement('div');
        card.className = 'sector-card';
        card.setAttribute('data-id', sector.id);
        
        card.innerHTML = `
            <div class="sector-header">
                <i class="fas ${sector.icon || 'fa-building'} sector-icon"></i>
                <h3 class="sector-title">${sector.nom}</h3>
            </div>
            <div class="sector-body">
                <p class="sector-description">${sector.description || 'Aucune description'}</p>
                <div class="action-buttons">
                    <button class="btn-icon edit-btn" onclick="editSector(${sector.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-btn" onclick="deleteSector(${sector.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        sectorsGrid.appendChild(card);
    });
}

// Utilitaire debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Menu toggle
menuToggle?.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

// Ajouter cette fonction après les fonctions existantes
function initIconSelector() {
    const iconOptions = document.querySelectorAll('.icon-option');
    
    iconOptions.forEach(option => {
        option.addEventListener('click', () => {
            // Supprimer la sélection précédente
            iconOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Ajouter la sélection à l'option cliquée
            option.classList.add('selected');
            
            // Mettre à jour l'icône sélectionnée
            selectedIcon = option.dataset.icon;
        });
    });
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    loadSectors();
    // Gestionnaires d'événements pour le modal
    const addSectorBtn = document.getElementById('add-sector-btn');
    if (addSectorBtn) {
        addSectorBtn.addEventListener('click', () => {
            isEditing = false;
            openModal();
        });
    }

    const modal = document.getElementById('sectorModal');
    const closeModalBtn = modal?.querySelector('.close-modal');
    const cancelBtn = modal?.querySelector('#cancelBtn');

    [closeModalBtn, cancelBtn].forEach(btn => {
        btn?.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    initIconSelector();
});