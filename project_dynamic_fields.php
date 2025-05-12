<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: unauthorized.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'Superadmin';

require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Champs Dynamiques - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="css/variables.css" />
    <link rel="stylesheet" href="css/data-management.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .user-profile .jury-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: var(--text-light);
            font-weight: bold;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius-full);
            user-select: none;
        }
        /* Modal centré et design amélioré */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: var(--overlay);
        }
        .modal-content {
            background-color: var(--bg-primary);
            margin: 10% auto;
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-md);
            width: 400px;
            box-shadow: 0 5px 15px var(--shadow-color);
            position: relative;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
        }
        .modal-header h3 {
            margin: 0;
            color: var(--text-primary);
        }
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
            transition: color var(--transition-fast);
        }
        .close-modal:hover {
            color: var(--primary-dark);
        }
        .data-header {
            margin-bottom: var(--spacing-md);
        }
        .data-header .btn-primary {
            font-size: 16px;
            padding: 8px 12px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border-radius: var(--border-radius-sm);
            border: none;
            cursor: pointer;
            transition: background-color var(--transition-fast);
        }
        .data-header .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        .data-table .actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        .data-table .actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--text-secondary);
            transition: color var(--transition-fast);
            padding: 6px 10px;
            border-radius: var(--border-radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .data-table .actions button.btn-primary {
            background-color: var(--primary-color);
            color: var(--text-light);
        }
        .data-table .actions button.btn-primary:hover {
            background-color: var(--primary-dark);
            color: var(--text-light);
        }
        .data-table .actions button.btn-danger {
            background-color: var(--danger);
            color: var(--text-light);
        }
        .data-table .actions button.btn-danger:hover {
            background-color: #b02a2a;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <header>
                <h2>Gestion des Champs Dynamiques</h2>
                <div class="user-info" style="margin-top: var(--spacing-md);">
                    <div class="user-profile">
                        <div class="jury-avatar president"><?= htmlspecialchars(getInitials($userName)) ?></div>
                        <span style="color: var(--text-primary); margin-left: var(--spacing-sm);"><?= htmlspecialchars($userName) ?></span>
                    </div>
                </div>
            </header>

            <div class="data-header">
                <button id="addFieldBtn" class="btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un champ dynamique
                </button>
            </div>

            <div class="data-table-container">
                <table class="data-table" id="fieldsTable">
                    <thead>
                        <tr>
                            <th>Nom du champ</th>
                            <th>Type</th>
                            <th>Obligatoire</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic fields will be loaded here -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for Add/Edit Dynamic Field -->
    <div id="fieldModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Ajouter un champ dynamique</h3>
                <button type="button" class="close-modal btn-secondary" id="closeModalBtn" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="fieldForm">
                    <input type="hidden" id="fieldId" name="id" value="">
                    <div class="form-group">
                        <label for="field_name">Nom du champ</label>
                        <input type="text" id="field_name" name="field_name" required placeholder="Ex: Téléphone, Email, etc.">
                    </div>
                    <div class="form-group">
                        <label for="field_type">Type de champ</label>
                        <select id="field_type" name="field_type">
                            <option value="text">Texte</option>
                            <option value="number">Nombre</option>
                            <option value="date">Date</option>
                            <option value="email">Email</option>
                            <option value="tel">Téléphone</option>
                        </select>
                    </div>
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="is_required" name="is_required">
                            Champ obligatoire
                        </label>
                    </div>
                    <div class="form-actions" style="display: flex; gap: var(--spacing-sm);">
                        <button type="submit" class="btn-primary" id="submitBtn">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                        <button type="button" class="btn-secondary" id="cancelBtn">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const addFieldBtn = document.getElementById('addFieldBtn');
        const fieldModal = document.getElementById('fieldModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const modalTitle = document.getElementById('modalTitle');
        const fieldForm = document.getElementById('fieldForm');
        const submitBtn = document.getElementById('submitBtn');
        const fieldsTableBody = document.querySelector('#fieldsTable tbody');

        function openModal(edit = false) {
            fieldModal.style.display = 'block';
            if (edit) {
                modalTitle.textContent = 'Modifier le champ dynamique';
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Enregistrer';
            } else {
                modalTitle.textContent = 'Ajouter un champ dynamique';
                submitBtn.innerHTML = '<i class="fas fa-plus"></i> Ajouter';
                fieldForm.reset();
                fieldForm.id.value = '';
            }
        }

        function closeModal() {
            fieldModal.style.display = 'none';
        }

        addFieldBtn.addEventListener('click', () => openModal());

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        window.onclick = function(event) {
            if (event.target == fieldModal) {
                closeModal();
            }
        };

        function loadFields() {
            fetch('actions/get_dynamic_fields.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fieldsTableBody.innerHTML = '';
                        data.fields.forEach(field => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${field.field_name}</td>
                                <td>${field.field_type}</td>
                                <td>${field.is_required ? 'Oui' : 'Non'}</td>
                                <td>${new Date(field.created_at).toLocaleString('fr-FR')}</td>
                                <td class="actions">
                                    <button class="btn-primary" title="Modifier" onclick='editField(${JSON.stringify(field)})'><i class="fas fa-edit"></i></button>
                                    <button class="btn-danger" title="Supprimer" onclick="deleteField(${field.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            `;
                            fieldsTableBody.appendChild(tr);
                        });
                    } else {
                        alert('Erreur lors du chargement des champs dynamiques');
                    }
                });
        }

        fieldForm.addEventListener('submit', e => {
            e.preventDefault();
            const id = fieldForm.id.value;
            const action = id ? 'update' : 'create';
            const payload = {
                action: action,
                id: id,
                field_name: fieldForm.field_name.value.trim(),
                field_type: fieldForm.field_type.value,
                is_required: fieldForm.is_required.checked ? 1 : 0
            };

            fetch('actions/save_dynamic_field.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadFields();
                    closeModal();
                } else {
                    alert(data.message || 'Erreur lors de la sauvegarde');
                }
            });
        });

        window.editField = function(field) {
            openModal(true);
            fieldForm.id.value = field.id;
            fieldForm.field_name.value = field.field_name;
            fieldForm.field_type.value = field.field_type;
            fieldForm.is_required.checked = field.is_required == 1;
        };

        window.deleteField = function(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce champ ?')) {
                fetch('actions/delete_dynamic_field.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadFields();
                    } else {
                        alert(data.message || 'Erreur lors de la suppression');
                    }
                });
            }
        };

        loadFields();
    });
    </script>
</body>
</html>
