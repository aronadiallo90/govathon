document.addEventListener('DOMContentLoaded', function() {
    // Expose functions globally for inline onclick handlers
    window.editProject = editProject;
    window.viewProject = viewProject;
    window.deleteProject = deleteProject;
    // DOM Elements
    const projectsTableBody = document.getElementById('projects-table-body');
    const projectsTableHead = document.querySelector('.data-table thead tr');
    const addProjectBtn = document.getElementById('add-project-btn');
    const projectModal = document.getElementById('project-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const projectForm = document.getElementById('project-form');
    const dynamicFieldsContainer = document.getElementById('dynamic-fields');
    const filterSector = document.getElementById('filter-sector');
    const filterStatus = document.getElementById('filter-status');
    let currentPage = 1;
    const itemsPerPage = 10;

    // Load projects on page load
    loadProjects();

    // Event listeners
    addProjectBtn.addEventListener('click', () => {
        openModal();
    });

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    projectForm.addEventListener('submit', handleFormSubmit);

    if (filterSector) filterSector.addEventListener('change', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);

    document.querySelectorAll('.pagination button').forEach(button => {
        button.addEventListener('click', () => {
            if (button.querySelector('.fa-chevron-left')) {
                if (currentPage > 1) currentPage--;
            } else {
                currentPage++;
            }
            loadProjects();
        });
    });

    // Functions
    // Fonction de filtrage
    function applyFilters() {
        const sector = filterSector.value;
        const status = filterStatus.value;
        
        const params = new URLSearchParams();
        if (sector !== 'all') params.append('sector', sector);
        if (status !== 'all') params.append('status', status);
        
        loadProjects(params);
    }

    async function loadProjects(params = new URLSearchParams()) {
        try {
            const url = `actions/get_projects.php?${params.toString()}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (data.success) {
                renderDynamicHeaders(data.dynamicFields);
                renderDynamicFieldsForm(data.dynamicFields);
                displayProjects(data.projects, data.dynamicFields);
                updatePagination(data.projects.length);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        }
    }

    function renderDynamicHeaders(dynamicFields) {
        if (!projectsTableHead) return;
        // Remove existing dynamic headers if any
        const existingDynamicHeaders = projectsTableHead.querySelectorAll('.dynamic-header');
        existingDynamicHeaders.forEach(th => th.remove());

        // Insert dynamic headers before the last th (Actions)
        const actionsTh = projectsTableHead.querySelector('th:last-child');
        dynamicFields.forEach(field => {
            const th = document.createElement('th');
            th.classList.add('dynamic-header');
            th.textContent = field.field_name;
            projectsTableHead.insertBefore(th, actionsTh);
        });
    }

    function renderDynamicFieldsForm(dynamicFields) {
        if (!dynamicFieldsContainer) return;
        dynamicFieldsContainer.innerHTML = '';

        dynamicFields.forEach(field => {
            const formGroup = document.createElement('div');
            formGroup.classList.add('form-group');

            const label = document.createElement('label');
            label.setAttribute('for', `dynamic-field-${field.id}`);
            label.textContent = field.field_name;
            formGroup.appendChild(label);

            let inputElement;
            switch (field.field_type) {
                case 'textarea':
                    inputElement = document.createElement('textarea');
                    inputElement.rows = 4;
                    break;
                case 'select':
                    inputElement = document.createElement('select');
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Sélectionner...';
                    inputElement.appendChild(defaultOption);
                    // TODO: Add options if needed
                    break;
                default:
                    inputElement = document.createElement('input');
                    inputElement.type = field.field_type;
            }
            inputElement.id = `dynamic-field-${field.id}`;
            inputElement.name = `dynamic_fields[${field.id}]`;
            if (field.is_required) {
                inputElement.required = true;
            }
            formGroup.appendChild(inputElement);

            dynamicFieldsContainer.appendChild(formGroup);
        });
    }

    function displayProjects(projects, dynamicFields) {
        if (!projectsTableBody) return;
        
        projectsTableBody.innerHTML = '';
        projects.forEach(project => {
            const row = createProjectRow(project, dynamicFields);
            projectsTableBody.appendChild(row);
        });
    }

function createProjectRow(project, dynamicFields) {
        const row = document.createElement('tr');
        
        let dynamicFieldsHtml = '';
        dynamicFields.forEach(field => {
            // project.dynamic_values est déjà un objet depuis le serveur
            const value = project.dynamic_values && project.dynamic_values[field.id] ? 
                project.dynamic_values[field.id] : '';
            dynamicFieldsHtml += `<td>${value}</td>`;
        });

        row.innerHTML = `
            <td>${project.id}</td>
            <td>${project.nom || ''}</td>
            <td>${project.created_by_email || 'N/A'}</td>
            <td>${project.secteur_nom || 'N/A'}</td>
            <td>${formatDate(project.created_at)}</td>
            <td>
                <span class="status-badge ${project.status}">
                    ${project.status ? getStatusLabel(project.status) : 'N/A'}
                </span>
            </td>
            <td>${project.note_moyenne ? Number(project.note_moyenne).toFixed(2) : '0.00'}</td>
            ${dynamicFieldsHtml}
            <td class="actions">
                <div class="action-buttons">
                    <button class="btn-icon view-btn" onclick="viewProject(${project.id})" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit-btn" onclick="editProject(${project.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-btn" onclick="deleteProject(${project.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        return row;
    }

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

    function openModal() {
        if (projectModal) {
            projectModal.style.display = 'block';
            projectForm.reset();
            document.getElementById('project-id').value = '';
            document.querySelector('.modal-header h3').textContent = 'Ajouter un projet';
        }
    }

    function closeModal() {
        if (projectModal) {
            projectModal.style.display = 'none';
            projectForm.reset();
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = {
            project_name: document.getElementById('project-name').value.trim(),
            project_description: document.getElementById('project-description').value.trim(),
            project_sector: document.getElementById('project-sector').value,
            dynamic_fields: {}
        };

        const dynamicInputs = document.querySelectorAll('#dynamic-fields [name^="dynamic_fields"]');
        dynamicInputs.forEach(input => {
            const fieldId = input.name.match(/\[(\d+)\]/)[1];
            formData.dynamic_fields[fieldId] = input.value.trim();
        });

        if (document.getElementById('project-id').value) {
            formData.id = document.getElementById('project-id').value;
        }

        try {
            const response = await fetch('actions/save_project.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                await loadProjects();
                closeModal();
                showNotification(data.message, 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    function formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString('fr-FR', options);
    }

    function getStatusLabel(status) {
        const labels = {
            'draft': 'Brouillon',
            'submitted': 'Soumis',
            'under_review': "En cours d'évaluation",
            'approved': 'Approuvé',
            'rejected': 'Rejeté'
        };
        return labels[status] || status;
    }

    async function editProject(id) {
        try {
            const response = await fetch(`actions/get_project.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const project = data.project;
                openModal();
                document.getElementById('project-id').value = project.id;
                document.getElementById('project-name').value = project.nom;
                document.getElementById('project-description').value = project.description;
                document.getElementById('project-sector').value = project.secteur_id;
                // Removed project-status field usage as it is not in the form anymore
                // document.getElementById('project-status').value = project.status;

                if (project.dynamic_fields) {
                    const fields = project.dynamic_fields.split('||');
                    fields.forEach(field => {
                        const [fieldName, value] = field.split(':');
                        const input = document.querySelector(`[name="dynamic_fields[${fieldName}]"]`);
                        if (input) input.value = value;
                    });
                }

                document.querySelector('.modal-header h3').textContent = 'Modifier le projet';

                // Enable inputs for editing
                enableFormInputs(true);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    async function viewProject(id) {
        try {
            const response = await fetch(`actions/get_project.php?id=${id}`);
            const data = await response.json();

            if (data.success) {
                const project = data.project;
                openModal();
                document.getElementById('project-id').value = project.id;
                document.getElementById('project-name').value = project.nom;
                document.getElementById('project-description').value = project.description;
                document.getElementById('project-sector').value = project.secteur_id;

                if (project.dynamic_fields) {
                    const fields = project.dynamic_fields.split('||');
                    fields.forEach(field => {
                        const [fieldName, value] = field.split(':');
                        const input = document.querySelector(`[name="dynamic_fields[${fieldName}]"]`);
                        if (input) input.value = value;
                    });
                }

                document.querySelector('.modal-header h3').textContent = 'Détails du projet';

                // Disable inputs for viewing
                enableFormInputs(false);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    function enableFormInputs(enable) {
        const inputs = projectForm.querySelectorAll('input, textarea, select, button');
        inputs.forEach(input => {
            if (input.id === 'cancel-btn') {
                input.disabled = false; // Always enable cancel button
            } else if (input.type === 'submit') {
                input.style.display = enable ? 'inline-block' : 'none'; // Show submit only if enabled
            } else {
                input.disabled = !enable;
            }
        });
    }

    async function deleteProject(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')) return;
        
        try {
            const response = await fetch('actions/delete_project.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                await loadProjects();
                showNotification('Projet supprimé avec succès');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    }

    function updatePagination(totalItems) {
        // Pagination logic here
    }
});
