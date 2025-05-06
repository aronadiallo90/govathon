// Variables globales
let votes = [];
let projects = [];
let juries = [];

// Données de base pour les projets
const sampleProjects = [
    { id: 1, name: "Smart City", team: "Équipe Alpha", sector: "Smart City", submissionDate: "2023-05-15", status: "approved", averageScore: 8.5 },
    { id: 2, name: "Santé Connectée", team: "Équipe Beta", sector: "Santé", submissionDate: "2023-05-20", status: "pending", averageScore: 7.2 },
    { id: 3, name: "Éducation Numérique", team: "Équipe Gamma", sector: "Éducation", submissionDate: "2023-05-25", status: "approved", averageScore: 9.0 },
    { id: 4, name: "Transport Vert", team: "Équipe Delta", sector: "Transport", submissionDate: "2023-06-01", status: "rejected", averageScore: 6.8 },
    { id: 5, name: "Culture Digitale", team: "Équipe Epsilon", sector: "Culture", submissionDate: "2023-06-05", status: "pending", averageScore: 7.5 },
    { id: 6, name: "Environnement Durable", team: "Équipe Zeta", sector: "Environnement", submissionDate: "2023-06-10", status: "approved", averageScore: 8.7 }
];

// Données de base pour les jurys
const sampleJuries = [
    { id: 1, name: "Dr. Sophie Martin", role: "Expert en Innovation", avatar: "https://randomuser.me/api/portraits/women/1.jpg" },
    { id: 2, name: "Prof. Jean Dupont", role: "Expert en Faisabilité", avatar: "https://randomuser.me/api/portraits/men/2.jpg" },
    { id: 3, name: "Mme. Claire Dubois", role: "Expert en Impact", avatar: "https://randomuser.me/api/portraits/women/3.jpg" },
    { id: 4, name: "M. Pierre Lambert", role: "Expert en Technologie", avatar: "https://randomuser.me/api/portraits/men/4.jpg" },
    { id: 5, name: "Dr. Marie Leroy", role: "Expert en Santé", avatar: "https://randomuser.me/api/portraits/women/5.jpg" }
];

// Données de base pour les votes
const sampleVotes = [
    { id: 1, projectId: 1, juryId: 1, innovation: 9, feasibility: 8, impact: 9, comments: "Excellent projet avec un fort potentiel d'innovation.", date: "2023-06-15" },
    { id: 2, projectId: 1, juryId: 2, innovation: 8, feasibility: 7, impact: 8, comments: "Bonne faisabilité technique mais quelques défis à relever.", date: "2023-06-16" },
    { id: 3, projectId: 1, juryId: 3, innovation: 9, feasibility: 8, impact: 9, comments: "Impact social très important, projet prometteur.", date: "2023-06-17" },
    { id: 4, projectId: 2, juryId: 1, innovation: 7, feasibility: 8, impact: 7, comments: "Innovation intéressante mais pourrait être améliorée.", date: "2023-06-18" },
    { id: 5, projectId: 2, juryId: 2, innovation: 7, feasibility: 8, impact: 7, comments: "Bonne faisabilité technique, quelques ajustements nécessaires.", date: "2023-06-19" },
    { id: 6, projectId: 3, juryId: 1, innovation: 9, feasibility: 9, impact: 9, comments: "Projet exceptionnel dans tous les domaines.", date: "2023-06-20" },
    { id: 7, projectId: 3, juryId: 2, innovation: 9, feasibility: 9, impact: 9, comments: "Très bonne faisabilité technique, projet bien conçu.", date: "2023-06-21" },
    { id: 8, projectId: 3, juryId: 3, innovation: 9, feasibility: 9, impact: 9, comments: "Impact social majeur, projet à fort potentiel.", date: "2023-06-22" },
    { id: 9, projectId: 4, juryId: 1, innovation: 6, feasibility: 7, impact: 7, comments: "Innovation limitée, mais projet viable.", date: "2023-06-23" },
    { id: 10, projectId: 4, juryId: 2, innovation: 6, feasibility: 6, impact: 7, comments: "Faisabilité technique moyenne, plusieurs défis à relever.", date: "2023-06-24" },
    { id: 11, projectId: 5, juryId: 1, innovation: 8, feasibility: 7, impact: 7, comments: "Bonne innovation, mais quelques points à améliorer.", date: "2023-06-25" },
    { id: 12, projectId: 5, juryId: 2, innovation: 7, feasibility: 8, impact: 7, comments: "Bonne faisabilité technique, projet prometteur.", date: "2023-06-26" },
    { id: 13, projectId: 6, juryId: 1, innovation: 9, feasibility: 8, impact: 9, comments: "Excellente innovation, projet très prometteur.", date: "2023-06-27" },
    { id: 14, projectId: 6, juryId: 2, innovation: 8, feasibility: 9, impact: 8, comments: "Très bonne faisabilité technique, projet bien conçu.", date: "2023-06-28" },
    { id: 15, projectId: 6, juryId: 3, innovation: 9, feasibility: 8, impact: 9, comments: "Impact social majeur, projet à fort potentiel.", date: "2023-06-29" }
];

// Éléments DOM
const votesTableBody = document.getElementById('votes-table-body');
const addVoteBtn = document.getElementById('add-vote-btn');
const voteModal = document.getElementById('vote-modal');
const closeModalBtn = document.querySelector('.close-modal');
const cancelBtn = document.getElementById('cancel-btn');
const voteForm = document.getElementById('vote-form');
const searchInput = document.querySelector('.search-bar input');
const projectFilter = document.getElementById('projectFilter');
const juryFilter = document.getElementById('juryFilter');
const dateFilter = document.getElementById('dateFilter');
const filterBtn = document.querySelector('.data-filters .btn-secondary');

// Initialisation
window.addEventListener('DOMContentLoaded', () => {
    loadData();
    setupEventListeners();
});

// Chargement des données
async function loadData() {
    // Utiliser uniquement les données locales pour le développement front-end
    projects = sampleProjects;
    juries = sampleJuries;
    votes = sampleVotes;
    populateProjectFilter();
    populateJuryFilter();
    displayVotes();
}

// Configuration des écouteurs d'événements
function setupEventListeners() {
    // Bouton d'ajout de vote
    if (!addVoteBtn) {
        alert("Le bouton 'Ajouter un vote' (id=add-vote-btn) est introuvable dans le DOM !");
        console.error("Le bouton 'Ajouter un vote' (id=add-vote-btn) est introuvable dans le DOM !");
        return;
    } else {
        console.log("Bouton 'Ajouter un vote' trouvé, ajout de l'écouteur d'événement...");
    }
    addVoteBtn.addEventListener('click', () => {
        console.log("Clic sur 'Ajouter un vote' détecté, ouverture de la modale...");
        showModal();
    });
    
    // Fermeture de la modale
    closeModalBtn.addEventListener('click', () => hideModal());
    cancelBtn.addEventListener('click', () => hideModal());
    
    // Soumission du formulaire
    voteForm.addEventListener('submit', handleVoteSubmit);
    
    // Filtres
    searchInput.addEventListener('input', filterVotes);
    filterBtn.addEventListener('click', filterVotes);
    
    // Mise à jour des curseurs
    document.querySelectorAll('.vote-slider input').forEach(slider => {
        slider.addEventListener('input', function() {
            this.nextElementSibling.textContent = this.value;
        });
    });
    
    // Menu toggle pour la sidebar
    document.getElementById('menu-toggle').addEventListener('click', toggleSidebar);
}

// Toggle de la sidebar
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Gestion de la modale
function showModal(vote = null) {
    if (vote) {
        // Mode édition
        document.getElementById('voteId').value = vote.id;
        document.getElementById('projectSelect').value = vote.projectId;
        document.getElementById('jurySelect').value = vote.juryId;
        document.getElementById('innovation').value = vote.innovation;
        document.getElementById('feasibility').value = vote.feasibility;
        document.getElementById('impact').value = vote.impact;
        document.getElementById('comments').value = vote.comments;
        
        // Mettre à jour les affichages des curseurs
        document.querySelectorAll('.vote-slider input').forEach(slider => {
            slider.nextElementSibling.textContent = slider.value;
        });
        
        document.querySelector('.modal-header h3').textContent = 'Modifier un vote';
    } else {
        // Mode création
        voteForm.reset();
        document.getElementById('voteId').value = '';
        
        document.querySelectorAll('.vote-slider input').forEach(slider => {
            slider.nextElementSibling.textContent = slider.value;
        });
        
        document.querySelector('.modal-header h3').textContent = 'Ajouter un vote';
    }
    
    const modal = document.getElementById('vote-modal');
    if (modal) {
        modal.classList.add('show');
    }
}

function hideModal() {
    const modal = document.getElementById('vote-modal');
    if (modal) {
        modal.classList.remove('show');
        voteForm.reset();
    }
}

// Gestion du formulaire
async function handleVoteSubmit(e) {
    e.preventDefault();
    const formData = new FormData(voteForm);
    const voteData = {
        id: formData.get('voteId'),
        projectId: formData.get('projectSelect'),
        juryId: formData.get('jurySelect'),
        innovation: parseInt(formData.get('innovation')),
        feasibility: parseInt(formData.get('feasibility')),
        impact: parseInt(formData.get('impact')),
        comments: formData.get('comments'),
        date: new Date().toISOString()
    };

    try {
        // Simuler l'envoi à l'API
        if (voteData.id) {
            // Mode édition
            const index = votes.findIndex(v => v.id === parseInt(voteData.id));
            if (index !== -1) {
                votes[index] = { ...votes[index], ...voteData };
            }
        } else {
            // Mode création
            const newId = Math.max(...votes.map(v => v.id)) + 1;
            votes.push({ ...voteData, id: newId });
        }
        
        hideModal();
        displayVotes();
        showNotification('Vote enregistré avec succès', 'success');
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'enregistrement du vote', 'error');
    }
}

// Affichage des votes
function displayVotes(filteredVotes = votes) {
    votesTableBody.innerHTML = '';
    
    if (filteredVotes.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="7" class="no-data">Aucun vote trouvé</td>`;
        votesTableBody.appendChild(row);
        return;
    }
    
    filteredVotes.forEach(vote => {
        const project = projects.find(p => p.id === vote.projectId);
        const jury = juries.find(j => j.id === vote.juryId);
        
        // Calculer la note moyenne
        const averageScore = ((vote.innovation + vote.feasibility + vote.impact) / 3).toFixed(1);
        
        // Déterminer la classe de couleur pour la note
        let scoreClass = '';
        if (averageScore >= 8) {
            scoreClass = 'score-high';
        } else if (averageScore >= 6) {
            scoreClass = 'score-medium';
        } else {
            scoreClass = 'score-low';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="project-info">
                    <span class="project-name">${project ? project.name : 'N/A'}</span>
                    <span class="project-sector">${project ? project.sector : ''}</span>
                </div>
            </td>
            <td>
                <span class="jury-name">${jury ? jury.name : 'N/A'}</span>
            </td>
            <td><span class="score ${scoreClass}">${vote.innovation}/10</span></td>
            <td><span class="score ${scoreClass}">${vote.feasibility}/10</span></td>
            <td><span class="score ${scoreClass}">${vote.impact}/10</span></td>
            <td>${new Date(vote.date).toLocaleDateString()}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon edit-btn" onclick="editVote(${vote.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-btn" onclick="deleteVote(${vote.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        votesTableBody.appendChild(row);
    });
}

// Filtrage des votes
function filterVotes() {
    const searchTerm = searchInput.value.toLowerCase();
    const projectId = projectFilter.value;
    const juryId = juryFilter.value;
    const date = dateFilter.value;

    const filteredVotes = votes.filter(vote => {
        const project = projects.find(p => p.id === vote.projectId);
        const jury = juries.find(j => j.id === vote.juryId);
        
        const matchesSearch = project?.name.toLowerCase().includes(searchTerm) ||
                            jury?.name.toLowerCase().includes(searchTerm);
        const matchesProject = !projectId || vote.projectId === parseInt(projectId);
        const matchesJury = !juryId || vote.juryId === parseInt(juryId);
        const matchesDate = !date || vote.date.startsWith(date);

        return matchesSearch && matchesProject && matchesJury && matchesDate;
    });

    displayVotes(filteredVotes);
}

// Suppression d'un vote
async function deleteVote(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce vote ?')) {
        try {
            // Simuler la suppression
            votes = votes.filter(v => v.id !== id);
            displayVotes();
            showNotification('Vote supprimé avec succès', 'success');
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la suppression du vote', 'error');
        }
    }
}

// Édition d'un vote
function editVote(id) {
    const vote = votes.find(v => v.id === id);
    if (vote) {
        showModal(vote);
    }
}

// Remplissage des filtres
function populateProjectFilter() {
    projectFilter.innerHTML = '<option value="">Tous les projets</option>';
    projects.forEach(project => {
        projectFilter.innerHTML += `<option value="${project.id}">${project.name}</option>`;
    });
    // Remplir aussi le select du formulaire
    const projectSelect = document.getElementById('projectSelect');
    if (projectSelect) {
        projectSelect.innerHTML = '<option value="">Sélectionner un projet</option>';
        projects.forEach(project => {
            projectSelect.innerHTML += `<option value="${project.id}">${project.name}</option>`;
        });
    }
}

function populateJuryFilter() {
    juryFilter.innerHTML = '<option value="">Tous les jurys</option>';
    juries.forEach(jury => {
        juryFilter.innerHTML += `<option value="${jury.id}">${jury.name}</option>`;
    });
    // Remplir aussi le select du formulaire
    const jurySelect = document.getElementById('jurySelect');
    if (jurySelect) {
        jurySelect.innerHTML = '<option value="">Sélectionner un jury</option>';
        juries.forEach(jury => {
            jurySelect.innerHTML += `<option value="${jury.id}">${jury.name}</option>`;
        });
    }
}

// Notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Écouteurs d'événements
document.addEventListener('DOMContentLoaded', function() {
    // Bouton Ajouter
    const addBtn = document.getElementById('add-vote-btn');
    if (addBtn) {
        addBtn.addEventListener('click', () => showModal());
    }

    // Bouton Fermer
    const closeBtn = document.querySelector('.close-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', hideModal);
    }

    // Fermer le modal en cliquant à l'extérieur
    const modal = document.getElementById('vote-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });
    }
});


