document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la navigation des paramètres
    const settingsNavItems = document.querySelectorAll('.settings-nav li');
    const settingsSections = document.querySelectorAll('.settings-section');

    settingsNavItems.forEach(item => {
        item.addEventListener('click', function() {
            // Retirer la classe active de tous les éléments
            settingsNavItems.forEach(navItem => navItem.classList.remove('active'));
            settingsSections.forEach(section => section.classList.remove('active'));

            // Ajouter la classe active à l'élément cliqué
            this.classList.add('active');

            // Afficher la section correspondante
            const targetSection = this.getAttribute('data-section');
            document.querySelector(`.settings-section[data-section="${targetSection}"]`).classList.add('active');
        });
    });

    // Gestion des thèmes
    const themeOptions = document.querySelectorAll('.theme-option');
    themeOptions.forEach(option => {
        option.addEventListener('click', function() {
            themeOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            const theme = this.getAttribute('data-theme');
            applyTheme(theme);
        });
    });

    // Gestion des couleurs
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            colorOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            const color = this.getAttribute('data-color');
            applyColor(color);
        });
    });

    // Gestion du changement d'image de profil
    const profileImageInput = document.querySelector('#profile-image-input');
    const profileImage = document.querySelector('.profile-image');
    
    if (profileImageInput && profileImage) {
        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Gestion des formulaires
    const settingsForms = document.querySelectorAll('.settings-form');
    settingsForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings(this);
        });
    });
});

// Fonction pour appliquer le thème
function applyTheme(theme) {
    const root = document.documentElement;
    switch(theme) {
        case 'light':
            root.style.setProperty('--bg-color', '#ffffff');
            root.style.setProperty('--text-color', '#333333');
            break;
        case 'dark':
            root.style.setProperty('--bg-color', '#1a1a1a');
            root.style.setProperty('--text-color', '#ffffff');
            break;
        case 'system':
            // Détecter les préférences système
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                root.style.setProperty('--bg-color', '#1a1a1a');
                root.style.setProperty('--text-color', '#ffffff');
            } else {
                root.style.setProperty('--bg-color', '#ffffff');
                root.style.setProperty('--text-color', '#333333');
            }
            break;
    }
    // Sauvegarder le thème dans le localStorage
    localStorage.setItem('theme', theme);
}

// Fonction pour appliquer la couleur
function applyColor(color) {
    const root = document.documentElement;
    root.style.setProperty('--primary-color', color);
    // Sauvegarder la couleur dans le localStorage
    localStorage.setItem('primary-color', color);
}

// Fonction pour sauvegarder les paramètres
function saveSettings(form) {
    const formData = new FormData(form);
    const settings = {};
    
    formData.forEach((value, key) => {
        settings[key] = value;
    });

    // Sauvegarder dans le localStorage
    localStorage.setItem('userSettings', JSON.stringify(settings));

    // Afficher une notification de succès
    showNotification('Paramètres sauvegardés avec succès !');
}

// Fonction pour afficher une notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Ajouter les styles pour l'animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Supprimer la notification après 3 secondes
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Charger les paramètres sauvegardés au chargement de la page
function loadSavedSettings() {
    // Charger le thème
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        const themeOption = document.querySelector(`.theme-option[data-theme="${savedTheme}"]`);
        if (themeOption) {
            themeOption.classList.add('active');
            applyTheme(savedTheme);
        }
    }

    // Charger la couleur
    const savedColor = localStorage.getItem('primary-color');
    if (savedColor) {
        const colorOption = document.querySelector(`.color-option[data-color="${savedColor}"]`);
        if (colorOption) {
            colorOption.classList.add('active');
            applyColor(savedColor);
        }
    }

    // Charger les autres paramètres
    const savedSettings = localStorage.getItem('userSettings');
    if (savedSettings) {
        const settings = JSON.parse(savedSettings);
        Object.entries(settings).forEach(([key, value]) => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = value === 'true';
                } else {
                    input.value = value;
                }
            }
        });
    }
}

// Appeler la fonction de chargement des paramètres au chargement de la page
document.addEventListener('DOMContentLoaded', loadSavedSettings); 