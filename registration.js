document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('teamRegistrationForm');
    const addMemberButton = document.getElementById('addMember');
    const teamMembersContainer = document.getElementById('teamMembers');
    let memberCount = 0;

    // Fonction pour créer un nouveau membre
    function createMemberElement() {
        memberCount++;
        const memberDiv = document.createElement('div');
        memberDiv.className = 'team-member';
        memberDiv.innerHTML = `
            <h3>Membre ${memberCount}</h3>
            <div class="form-group">
                <label for="member${memberCount}Name">Nom complet</label>
                <input type="text" id="member${memberCount}Name" name="member${memberCount}Name" required>
                <span class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="member${memberCount}Email">Email</label>
                <input type="email" id="member${memberCount}Email" name="member${memberCount}Email" required>
                <span class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="member${memberCount}Phone">Téléphone</label>
                <input type="tel" id="member${memberCount}Phone" name="member${memberCount}Phone" required>
                <span class="error-message"></span>
            </div>
            ${memberCount > 1 ? '<button type="button" class="btn btn-danger remove-member">Supprimer</button>' : ''}
        `;
        return memberDiv;
    }

    // Validation des champs
    function validateField(input) {
        const errorMessage = input.nextElementSibling;
        let isValid = true;
        let message = '';

        // Réinitialiser le style
        input.classList.remove('error');
        errorMessage.textContent = '';

        // Validation selon le type de champ
        switch (input.type) {
            case 'text':
                if (input.value.length < 2) {
                    isValid = false;
                    message = 'Le nom doit contenir au moins 2 caractères';
                }
                break;
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    message = 'Veuillez entrer une adresse email valide';
                }
                break;
            case 'tel':
                const phoneRegex = /^(\+33|0)[1-9](\d{2}){4}$/;
                if (!phoneRegex.test(input.value)) {
                    isValid = false;
                    message = 'Veuillez entrer un numéro de téléphone valide';
                }
                break;
        }

        // Afficher l'erreur si nécessaire
        if (!isValid) {
            input.classList.add('error');
            errorMessage.textContent = message;
        }

        return isValid;
    }

    // Ajouter un membre
    addMemberButton.addEventListener('click', () => {
        const memberElement = createMemberElement();
        teamMembersContainer.appendChild(memberElement);
        
        // Ajouter les écouteurs d'événements pour la validation
        const inputs = memberElement.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    validateField(input);
                }
            });
        });
    });

    // Supprimer un membre
    teamMembersContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-member')) {
            e.target.closest('.team-member').remove();
            memberCount--;
            // Mettre à jour les numéros des membres
            const members = teamMembersContainer.querySelectorAll('.team-member');
            members.forEach((member, index) => {
                member.querySelector('h3').textContent = `Membre ${index + 1}`;
            });
        }
    });

    // Gérer la soumission du formulaire
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Valider tous les champs
        let isValid = true;
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
            return;
        }
        
        // Récupérer les données du formulaire
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Ajouter les membres à l'objet data
        data.members = [];
        const members = teamMembersContainer.querySelectorAll('.team-member');
        members.forEach((member, index) => {
            data.members.push({
                name: formData.get(`member${index + 1}Name`),
                email: formData.get(`member${index + 1}Email`),
                phone: formData.get(`member${index + 1}Phone`)
            });
        });

        try {
            // Simuler l'envoi des données à un serveur
            console.log('Données du formulaire:', data);
            
            // Afficher un message de succès
            showNotification('Inscription réussie !', 'success');
            
            // Réinitialiser le formulaire
            form.reset();
            teamMembersContainer.innerHTML = '';
            memberCount = 0;
            teamMembersContainer.appendChild(createMemberElement());
            
        } catch (error) {
            showNotification('Erreur lors de l\'inscription. Veuillez réessayer.', 'error');
            console.error('Erreur:', error);
        }
    });

    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Supprimer la notification après 3 secondes
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Ajouter le premier membre par défaut
    teamMembersContainer.appendChild(createMemberElement());
}); 