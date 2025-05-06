// Données de démonstration
let mockData = [
    {
        id: 1,
        name: "Jean Dupont",
        type: "users",
        date: "2024-03-15",
        status: "active",
        description: "Utilisateur premium"
    },
    {
        id: 2,
        name: "iPhone 13 Pro",
        type: "products",
        date: "2024-03-14",
        status: "active",
        description: "Smartphone dernière génération"
    },
    {
        id: 3,
        name: "Commande #1234",
        type: "orders",
        date: "2024-03-13",
        status: "pending",
        description: "En attente de livraison"
    }
];

// Éléments DOM
const dataTableBody = document.getElementById('data-table-body');
const addDataBtn = document.getElementById('add-data-btn');
const dataModal = document.getElementById('data-modal');
const closeModalBtn = document.querySelector('.close-modal');
const cancelBtn = document.getElementById('cancel-btn');
const dataForm = document.getElementById('data-form');
const filterType = document.getElementById('filter-type');
const filterDate = document.getElementById('filter-date');

// Gestion du menu mobile
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');

menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});

document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});

// Fonctions de gestion des données
function renderData(data) {
    dataTableBody.innerHTML = data.map(item => `
        <tr>
            <td>${item.id}</td>
            <td>${item.name}</td>
            <td>${item.type}</td>
            <td>${item.date}</td>
            <td>
                <span class="status-badge status-${item.status}">
                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="edit-btn" onclick="editData(${item.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="delete-btn" onclick="deleteData(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openModal(mode = 'add', data = null) {
    const modalTitle = dataModal.querySelector('.modal-header h3');
    modalTitle.textContent = mode === 'add' ? 'Ajouter des données' : 'Modifier les données';
    
    if (data) {
        document.getElementById('data-name').value = data.name;
        document.getElementById('data-type').value = data.type;
        document.getElementById('data-status').value = data.status;
        document.getElementById('data-description').value = data.description;
    } else {
        dataForm.reset();
    }
    
    dataModal.classList.add('active');
}

function closeModal() {
    dataModal.classList.remove('active');
    dataForm.reset();
}

function addData(formData) {
    const newData = {
        id: mockData.length + 1,
        name: formData.get('data-name'),
        type: formData.get('data-type'),
        date: new Date().toISOString().split('T')[0],
        status: formData.get('data-status'),
        description: formData.get('data-description')
    };
    
    mockData.push(newData);
    renderData(mockData);
}

function editData(id) {
    const data = mockData.find(item => item.id === id);
    if (data) {
        openModal('edit', data);
    }
}

function deleteData(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette donnée ?')) {
        mockData = mockData.filter(item => item.id !== id);
        renderData(mockData);
    }
}

function filterData() {
    let filteredData = [...mockData];
    
    const typeFilter = filterType.value;
    const dateFilter = filterDate.value;
    
    if (typeFilter !== 'all') {
        filteredData = filteredData.filter(item => item.type === typeFilter);
    }
    
    if (dateFilter) {
        filteredData = filteredData.filter(item => item.date === dateFilter);
    }
    
    renderData(filteredData);
}

// Event Listeners
addDataBtn.addEventListener('click', () => openModal('add'));

closeModalBtn.addEventListener('click', closeModal);

cancelBtn.addEventListener('click', closeModal);

dataForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(dataForm);
    addData(formData);
    closeModal();
});

filterType.addEventListener('change', filterData);
filterDate.addEventListener('change', filterData);

// Initialisation
renderData(mockData); 