// Données des critères (à remplacer par des données réelles de l'API)
const criteriaData = [
    {
        id: 1,
        name: "Innovation et Créativité",
        weight: 30,
        items: [
            {
                id: 1,
                name: "Originalité de la solution",
                description: "Évaluation de l'unicité et de la créativité de la solution proposée",
                sector: "tech",
                stage: "qualification"
            },
            {
                id: 2,
                name: "Innovation technologique",
                description: "Utilisation de technologies innovantes et modernes",
                sector: "tech",
                stage: "qualification"
            }
        ]
    },
    {
        id: 2,
        name: "Viabilité et Impact",
        weight: 40,
        items: [
            {
                id: 3,
                name: "Modèle économique",
                description: "Évaluation de la viabilité économique du projet",
                sector: "tech",
                stage: "finale"
            }
        ]
    }
];

// Éléments DOM
const searchInput = document.querySelector('.search-bar input');
const sectorFilter = document.getElementById('sectorFilter');
const stageFilter = document.getElementById('stageFilter');
const criteriaList = document.querySelector('.criteria-list');
const modal = document.getElementById('criteriaModal');
const criteriaForm = document.getElementById('criteriaForm');
const closeModalBtn = document.querySelector('.close-modal');
const cancelBtn = document.getElementById('cancelBtn');

// État de l'application
let currentEditId = null;
let isEditing = false;

// Fonction pour afficher une notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Suppression après 3 secondes
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Fonction pour afficher les critères
function displayCriteria(criteria = criteriaData) {
    criteriaList.innerHTML = '';
    
    if (criteria.length === 0) {
        criteriaList.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>Aucun critère ne correspond à votre recherche</p>
            </div>
        `;
        return;
    }
    
    criteria.forEach(group => {
        const groupElement = document.createElement('div');
        groupElement.className = 'criteria-group';
        
        groupElement.innerHTML = `
            <div class="group-header">
                <h3>${group.name}</h3>
                <span class="weight">Poids: ${group.weight}%</span>
            </div>
            <div class="criteria-items">
                ${group.items.map(item => `
                    <div class="criteria-item" data-id="${item.id}">
                        <div class="criteria-info">
                            <h4>${item.name}</h4>
                            <p>${item.description}</p>
                            <div class="criteria-meta">
                                <span class="badge sector">${item.sector}</span>
                                <span class="badge stage">${item.stage}</span>
                            </div>
                        </div>
                        <div class="criteria-actions">
                            <button class="btn-icon edit-btn" title="Modifier" onclick="editCriteria(${item.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon delete-btn" title="Supprimer" onclick="deleteCriteria(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        
        criteriaList.appendChild(groupElement);
    });
}

// Fonction pour ouvrir le modal
function openModal(title = 'Ajouter un critère') {
    modal.querySelector('.modal-header h3').textContent = title;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Fonction pour fermer le modal
function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
    criteriaForm.reset();
    currentEditId = null;
    isEditing = false;
}

// Fonction pour éditer un critère
function editCriteria(id) {
    const criteria = criteriaData.find(group => 
        group.items.some(item => item.id === id)
    );
    
    if (criteria) {
        const item = criteria.items.find(item => item.id === id);
        currentEditId = id;
        isEditing = true;
        openModal('Modifier le critère');
        
        // Remplir le formulaire avec les données du critère
        document.getElementById('criteriaName').value = item.name;
        document.getElementById('criteriaDescription').value = item.description;
        document.getElementById('criteriaWeight').value = criteria.weight;
        document.getElementById('criteriaSector').value = item.sector;
        document.getElementById('criteriaStage').value = item.stage;
    }
}

// Fonction pour supprimer un critère
function deleteCriteria(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce critère ?')) {
        // Logique de suppression (à implémenter avec l'API)
        console.log('Suppression du critère:', id);
        
        // Mise à jour temporaire de l'interface
        const item = document.querySelector(`.criteria-item[data-id="${id}"]`);
        if (item) {
            item.style.opacity = '0';
            setTimeout(() => {
                displayCriteria();
                showNotification('Critère supprimé avec succès');
            }, 300);
        }
    }
}

// Gestionnaire de recherche avec debounce
let searchTimeout;
searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = e.target.value.toLowerCase();
        const filteredCriteria = criteriaData.map(group => ({
            ...group,
            items: group.items.filter(item =>
                item.name.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm)
            )
        })).filter(group => group.items.length > 0);
        
        displayCriteria(filteredCriteria);
    }, 300);
});

// Gestionnaires de filtres
sectorFilter.addEventListener('change', applyFilters);
stageFilter.addEventListener('change', applyFilters);

function applyFilters() {
    const sectorValue = sectorFilter.value;
    const stageValue = stageFilter.value;
    
    const filteredCriteria = criteriaData.map(group => ({
        ...group,
        items: group.items.filter(item =>
            (!sectorValue || item.sector === sectorValue) &&
            (!stageValue || item.stage === stageValue)
        )
    })).filter(group => group.items.length > 0);
    
    displayCriteria(filteredCriteria);
}

// Validation du formulaire
function validateForm(formData) {
    const errors = [];
    
    if (!formData.name.trim()) {
        errors.push('Le nom du critère est requis');
    }
    
    if (!formData.description.trim()) {
        errors.push('La description est requise');
    }
    
    if (formData.weight < 0 || formData.weight > 100) {
        errors.push('Le poids doit être compris entre 0 et 100');
    }
    
    if (!formData.sector) {
        errors.push('Le secteur est requis');
    }
    
    if (!formData.stage) {
        errors.push('L\'étape est requise');
    }
    
    return errors;
}

// Gestionnaire de soumission du formulaire
criteriaForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Récupération des données du formulaire
    const formData = {
        name: document.getElementById('criteriaName').value,
        description: document.getElementById('criteriaDescription').value,
        weight: parseInt(document.getElementById('criteriaWeight').value),
        sector: document.getElementById('criteriaSector').value,
        stage: document.getElementById('criteriaStage').value
    };
    
    // Validation
    const errors = validateForm(formData);
    if (errors.length > 0) {
        showNotification(errors.join('<br>'), 'error');
        return;
    }
    
    // Logique de sauvegarde (à implémenter avec l'API)
    console.log('Données du formulaire:', formData);
    
    // Mise à jour temporaire de l'interface
    if (isEditing) {
        showNotification('Critère modifié avec succès');
    } else {
        showNotification('Critère ajouté avec succès');
    }
    
    closeModal();
    displayCriteria();
});

// Gestionnaires d'événements pour le modal
closeModalBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeModal();
    }
});

// Gestionnaire pour le bouton d'ajout
// document.querySelector('.content-header').insertAdjacentHTML('beforeend', `
//     <div class="data-header">
//         <h2>Gestion des Critères</h2>
//         <button class="btn-primary" onclick="openModal()">
//             <i class="fas fa-plus"></i>
//             Ajouter un critère
//         </button>
//     </div>
// `);

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    displayCriteria();
    
    // Ajout des styles pour les notifications
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .notification.success {
            border-left: 4px solid #4caf50;
        }
        
        .notification.error {
            border-left: 4px solid #f44336;
        }
        
        .notification i {
            font-size: 1.2rem;
        }
        
        .notification.success i {
            color: #4caf50;
        }
        
        .notification.error i {
            color: #f44336;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .no-results i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .no-results p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .criteria-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .badge {
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge.sector {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge.stage {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .add-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .add-btn i {
            font-size: 1rem;
        }
    `;
    document.head.appendChild(style);
});