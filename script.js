// Gestion du menu mobile
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('.main-content');

menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('expanded');
});

// Fermer le menu si on clique en dehors
document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('active');
        mainContent.classList.remove('expanded');
    }
});

// Configuration des graphiques
const salesCtx = document.getElementById('salesChart').getContext('2d');
const productsCtx = document.getElementById('productsChart').getContext('2d');

// Graphique des ventes mensuelles
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
        datasets: [{
            label: 'Ventes',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: '#4361ee',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(67, 97, 238, 0.1)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    drawBorder: false
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Graphique de distribution des produits
const productsChart = new Chart(productsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Électronique', 'Vêtements', 'Alimentation', 'Accessoires'],
        datasets: [{
            data: [30, 25, 20, 25],
            backgroundColor: [
                '#4361ee',
                '#3f37c9',
                '#4895ef',
                '#560bad'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Animation des cartes de statistiques
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-5px)';
        card.style.transition = 'transform 0.3s ease';
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
    });
});

// Mise à jour en temps réel des activités
function updateActivities() {
    const activities = [
        {
            icon: 'fa-user-plus',
            text: 'Nouvel utilisateur inscrit',
            time: 'Il y a 5 minutes'
        },
        {
            icon: 'fa-shopping-bag',
            text: 'Nouvelle commande #1234',
            time: 'Il y a 15 minutes'
        },
        {
            icon: 'fa-comment',
            text: 'Nouveau commentaire',
            time: 'Il y a 30 minutes'
        }
    ];

    const activityList = document.querySelector('.activity-list');
    activityList.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="fas ${activity.icon}"></i>
            </div>
            <div class="activity-details">
                <p>${activity.text}</p>
                <span>${activity.time}</span>
            </div>
        </div>
    `).join('');
}

// Mettre à jour les activités toutes les 30 secondes
setInterval(updateActivities, 30000);

document.addEventListener('DOMContentLoaded', function() {
    // Données fictives pour les graphiques
    const sectorsData = {
        labels: ['Smart City', 'Santé', 'Éducation', 'Environnement', 'Transport', 'Culture'],
        datasets: [{
            label: 'Nombre de projets',
            data: [5, 4, 3, 6, 3, 3],
            backgroundColor: [
                'rgba(52, 152, 219, 0.8)',
                'rgba(46, 204, 113, 0.8)',
                'rgba(155, 89, 182, 0.8)',
                'rgba(241, 196, 15, 0.8)',
                'rgba(231, 76, 60, 0.8)',
                'rgba(52, 73, 94, 0.8)'
            ]
        }]
    };

    const criteriaData = {
        labels: ['Innovation', 'Faisabilité', 'Impact', 'Présentation', 'Technique'],
        datasets: [{
            label: 'Note moyenne',
            data: [8.5, 7.2, 8.8, 7.5, 8.0],
            backgroundColor: 'rgba(52, 152, 219, 0.8)',
            borderColor: 'rgba(52, 152, 219, 1)',
            borderWidth: 1
        }]
    };

    // Configuration des graphiques
    const sectorsChart = new Chart(document.getElementById('sectorsChart'), {
        type: 'bar',
        data: sectorsData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const criteriaChart = new Chart(document.getElementById('criteriaChart'), {
        type: 'radar',
        data: criteriaData,
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    });

    // Gestion des notifications
    const notificationBell = document.querySelector('.user-info .fa-bell');
    notificationBell.addEventListener('click', function() {
        // Simuler l'affichage des notifications
        alert('Notifications:\n- 3 nouveaux projets en attente\n- 5 votes à valider\n- Phase de vote terminée');
    });

    // Mise à jour automatique des données (simulation)
    setInterval(function() {
        // Mettre à jour les données des graphiques avec de petites variations
        sectorsData.datasets[0].data = sectorsData.datasets[0].data.map(value => 
            Math.max(0, value + (Math.random() > 0.7 ? 1 : 0))
        );
        criteriaData.datasets[0].data = criteriaData.datasets[0].data.map(value => 
            Math.min(10, Math.max(0, value + (Math.random() - 0.5)))
        );

        // Mettre à jour les graphiques
        sectorsChart.update();
        criteriaChart.update();
    }, 30000); // Mise à jour toutes les 30 secondes
}); 