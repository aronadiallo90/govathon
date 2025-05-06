// Charger la barre de navigation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner l'élément qui contiendra la navbar
    const sharedLayout = document.getElementById('shared-layout');
    
    if (sharedLayout) {
        // Charger le contenu de la navbar
        fetch('../navbar.html')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                // Insérer le HTML de la navbar
                sharedLayout.innerHTML = html;

                // Marquer le lien actif dans la navigation
                const currentPath = window.location.pathname;
                const currentPage = currentPath.split('/').pop() || 'index.html';
                const navLinks = document.querySelectorAll('.nav-links a');
                
                navLinks.forEach(link => {
                    if (link.getAttribute('href') === currentPage) {
                        link.parentElement.classList.add('active');
                    }
                });

                // Initialiser le toggle du menu
                const menuToggle = document.getElementById('menu-toggle');
                if (menuToggle) {
                    menuToggle.addEventListener('click', () => {
                        document.querySelector('.sidebar').classList.toggle('collapsed');
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement de la navbar:', error);
                sharedLayout.innerHTML = '<p>Erreur lors du chargement de la navigation</p>';
            });
    }
});