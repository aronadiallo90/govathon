document.addEventListener('DOMContentLoaded', () => {
    const projectModal = document.getElementById('project-modal');
    const addProjectBtn = document.getElementById('add-project-btn');
    const closeModalBtn = projectModal.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const projectForm = document.getElementById('project-form');
    const dynamicFieldsContainer = document.getElementById('dynamic-fields');

    // Open modal for adding new project
    addProjectBtn.addEventListener('click', () => {
        openModal();
        clearForm();
        // Set modal header title to "Ajouter un projet"
        const modalHeaderTitle = document.querySelector('#project-modal .modal-header h3');
        if (modalHeaderTitle) {
            modalHeaderTitle.textContent = 'Ajouter un projet';
        }
    });

    // Close modal
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Open modal function
    function openModal() {
        projectModal.style.display = 'block';
        projectForm.dataset.projectId = ''; // Clear project id for new project
    }

    // Close modal function
    function closeModal() {
        projectModal.style.display = 'none';
    }

    // Clear form inputs
    function clearForm() {
        projectForm.reset();
        // Clear dynamic fields inputs
        const inputs = dynamicFieldsContainer.querySelectorAll('input[type="text"]');
        inputs.forEach(input => input.value = '');
    }

    // Improved form submission with dynamic fields validation
    projectForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            // Récupérer tous les champs du formulaire
            const formData = {
                project_name: document.getElementById('project-name').value.trim(),
                project_description: document.getElementById('project-description').value.trim(),
                project_sector: document.getElementById('project-sector').value,
                project_status: document.getElementById('project-status').value || 'draft',
                dynamic_fields: {}
            };

            // Validation côté client
            if (!formData.project_name || !formData.project_description || !formData.project_sector) {
                throw new Error('Veuillez remplir tous les champs obligatoires');
            }

            // Récupérer les champs dynamiques
            const dynamicInputs = document.querySelectorAll('#dynamic-fields [name^="dynamic_fields"]');
            dynamicInputs.forEach(input => {
                const matches = input.name.match(/\[(\d+)\]/);
                if (matches) {
                    const fieldId = matches[1];
                    formData.dynamic_fields[fieldId] = input.value.trim();
                }
            });

            const response = await fetch('actions/save_project.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success) {
                showNotification(result.message, 'success');
                closeModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(result.message || 'Erreur lors de la sauvegarde');
            }
        } catch (error) {
            showNotification(error.message || 'Erreur lors de la sauvegarde du projet', 'error');
            console.error('Erreur:', error);
        }
    });

    // Helper function to show notifications
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

    // Load project data for editing
    window.loadProjectData = async (projectId) => {
        try {
            const response = await fetch(`get_project.php?id=${projectId}`);
            const project = await response.json();

            if (project) {
                openModal();
                projectForm.dataset.projectId = project.id;
                
                // Set basic fields
                projectForm.querySelector('[name="project-name"]').value = project.nom;
                projectForm.querySelector('[name="project-description"]').value = project.description;
                projectForm.querySelector('[name="project-sector"]').value = project.secteur_id;
                projectForm.querySelector('[name="project-status"]').value = project.status;

                // Set dynamic fields
                if (project.dynamic_fields) {
                    Object.entries(project.dynamic_fields).forEach(([fieldId, value]) => {
                        const input = dynamicFieldsContainer.querySelector(`[data-field-id="${fieldId}"]`);
                        if (input) input.value = value;
                    });
                }

                // Update modal title
                const modalHeaderTitle = document.querySelector('#project-modal .modal-header h3');
                if (modalHeaderTitle) {
                    modalHeaderTitle.textContent = 'Modifier le projet';
                }
            }
        } catch (error) {
            console.error(error);
            showNotification('Erreur lors du chargement du projet', 'error');
        }
    };
});
