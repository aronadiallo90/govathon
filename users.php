<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'Admin';

require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Utilisateurs - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="css/users.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Styles généraux */
        .data-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Style du bouton d'ajout */
        #add-user-btn {
            background-color: #00843F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        #add-user-btn:hover {
            background-color: #006632;
        }

        #add-user-btn i {
            font-size: 1.1em;
        }

        /* Styles du tableau */
        .data-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
            margin-top: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Styles des boutons d'action */
        .btn-icon {
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s;
        }

        .btn-icon:hover {
            color: #333;
        }

        .btn-icon.edit-btn:hover {
            color: #1976d2;
        }

        .btn-icon.delete-btn:hover {
            color: #d32f2f;
        }

        .btn-icon.view-btn:hover {
            color: #2e7d32;
        }

        /* Styles du modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            margin: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #495057;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* Styles du formulaire */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 1rem;
            color: #495057;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background-color: #00843F;
            color: white;
        }

        .btn-primary:hover {
            background-color: #006632;
        }

        .btn-primary:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        /* Styles des notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1001;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal {
                padding: 10px;
            }
            
            .modal-content {
                width: 95%;
                margin: 10px auto;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }

        /* Style du statut actif */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .status-badge.active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge.inactive {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Style du jury global */
        .jury-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .user-profile .jury-avatar {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            color: white;
            font-weight: bold;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            user-select: none;
        }

        .required {
            color: #dc3545;
            margin-left: 4px;
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <?php include 'components/header.php'; ?>

            <div class="data-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2>Gestion des Utilisateurs</h2>
                <button class="btn-primary" id="add-user-btn">
                    <i class="fas fa-plus"></i> Ajouter un utilisateur
                </button>
            </div>

            <div class="data-table-container">
                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Secteur</th>
                            <th>Actif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- Users will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for Add/Edit User -->
    <div class="modal" id="user-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Ajouter un utilisateur</h3>
                <button class="close-modal" id="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <input type="hidden" id="user-id" name="id" />
                    <div class="form-group">
                        <label for="user-name">Nom <span class="required">*</span></label>
                        <input type="text" id="user-name" name="name" required />
                    </div>
                    <div class="form-group">
                        <label for="user-email">Email <span class="required">*</span></label>
                        <input type="email" id="user-email" name="email" required />
                    </div>
                    <div class="form-group">
                        <label for="user-password">Mot de passe <span class="required">*</span></label>
                        <input type="password" id="user-password" name="password" />
                        <small class="form-text">Laissez vide pour conserver le mot de passe actuel lors de la modification</small>
                    </div>
                    <div class="form-group">
                        <label for="user-role">Rôle <span class="required">*</span></label>
                        <select id="user-role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="jury">Jury</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user-secteur">Secteur</label>
                        <select id="user-secteur" name="secteur_id">
                            <option value="">Aucun</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user-active">Actif</label>
                        <select id="user-active" name="is_active">
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user-global-jury">Jury Global</label>
                        <select id="user-global-jury" name="is_global_jury">
                            <option value="0">Non</option>
                            <option value="1">Oui</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/users.js"></script>
</body>
</html>
