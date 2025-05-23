<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

// Récupération des critères
$orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'nom';
$orderDir = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'DESC' : 'ASC';

// Validation des colonnes de tri autorisées
$allowedColumns = ['id', 'nom', 'coefficient', 'created_at', 'updated_at'];
if (!in_array($orderBy, $allowedColumns)) {
    $orderBy = 'nom';
}

$stmt = $pdo->prepare("SELECT * FROM criteres ORDER BY {$orderBy} {$orderDir}");
$stmt->execute();
$criteres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement des critères par catégorie (basé sur le nom pour cette démo)
$categories = [];
foreach ($criteres as $critere) {
    // Exemple simple: utiliser le premier mot du nom comme catégorie
    $firstWord = explode(' ', $critere['nom'])[0];
    if (!isset($categories[$firstWord])) {
        $categories[$firstWord] = [];
    }
    $categories[$firstWord][] = $critere;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Critères - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Styles généraux */
        .criteres-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .criteres-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Style du bouton d'ajout */
        #add-critere-btn {
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
            transition: background-color 0.2s, transform 0.2s;
        }

        #add-critere-btn:hover {
            background-color: #006632;
            transform: translateY(-2px);
        }

        #add-critere-btn i {
            font-size: 1.1em;
        }

        /* Styles des filtres et options de tri */
        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .search-filter {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input:focus {
            border-color: #00843F;
            box-shadow: 0 0 0 2px rgba(0,132,63,0.2);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-select {
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
            background-color: white;
        }

        .view-options {
            display: flex;
            gap: 5px;
        }

        .view-btn {
            background: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 10px;
            cursor: pointer;
            color: #666;
            transition: all 0.2s;
        }

        .view-btn:hover {
            background-color: #f0f0f0;
        }

        .view-btn.active {
            background-color: #00843F;
            color: white;
            border-color: #00843F;
        }

        /* Styles des vues */
        .view-mode {
            display: none;
        }

        .view-mode.active {
            display: block;
        }

        /* Vue en grille */
        .criteres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .critere-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .critere-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .critere-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .critere-info h3 {
            margin: 0;
            color: #333;
            font-size: 1.2em;
        }

        .critere-coefficient {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .critere-actions {
            display: flex;
            gap: 8px;
        }

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

        .btn-icon.edit-critere:hover {
            color: #1976d2;
        }

        .btn-icon.delete-critere:hover {
            color: #d32f2f;
        }

        .critere-details {
            color: #666;
        }

        .critere-details p {
            margin: 10px 0;
            line-height: 1.4;
        }

        .critere-meta {
            display: flex;
            justify-content: flex-end;
            font-size: 0.8em;
            color: #888;
            margin-top: 10px;
        }

        /* Vue en liste */
        .criteres-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .criteres-table th,
        .criteres-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .criteres-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .criteres-table tr:hover {
            background-color: #f5f5f5;
        }

        .description-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Vue par catégories */
        .criteres-categories {
            margin-top: 20px;
        }

        .category-section {
            margin-bottom: 30px;
        }

        .category-title {
            font-size: 1.3em;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #00843F;
            margin-bottom: 15px;
        }

        .category-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        /* Styles du modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-20px);
            transition: transform 0.3s;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #666;
        }

        /* Styles du formulaire */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
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
        
        .validation-feedback {
            font-size: 0.875rem;
            margin-top: 5px;
            min-height: 18px;
        }
        
        .validation-feedback.error {
            color: #dc3545;
        }
        
        .validation-feedback.success {
            color: #28a745;
        }
        
        input:invalid, textarea:invalid {
            border-color: #dc3545;
        }
        
        input:focus:invalid, textarea:focus:invalid {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        input:valid:not(:placeholder-shown), textarea:valid:not(:placeholder-shown) {
            border-color: #28a745;
        }
        
        input:focus:valid:not(:placeholder-shown), textarea:focus:valid:not(:placeholder-shown) {
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
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

        .notification.success {
            background-color: #2e7d32;
        }

        .notification.error {
            background-color: #d32f2f;
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

        /* Styles responsifs */
        @media (max-width: 992px) {
            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .sort-options, .view-options {
                justify-content: space-between;
                width: 100%;
            }
            
            .category-items {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .criteres-header, .filters-container {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            .criteres-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 480px) {
            .criteres-header h2 {
                font-size: 1.3rem;
            }
            .filters-container {
                padding: 10px;
            }
            .critere-card {
                padding: 10px;
            }
        }
        .modal.active {
            display: flex !important;
            justify-content: center;
            align-items: center;
            opacity: 1;
        }
        .modal-content {
            width: 95vw;
            max-width: 500px;
        }
        .btn, .btn-primary, .btn-secondary {
            min-width: 44px;
            min-height: 44px;
            font-size: 1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <?php include 'components/header.php'; ?>

            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Critères</h2>
                    <button id="add-critere-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un critère
                    </button>
                </div>

                <div class="filters-container">
                    <div class="search-filter">
                        <input type="text" id="search-critere" placeholder="Rechercher un critère..." class="search-input">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <div class="sort-options">
                        <label for="sort-select">Trier par:</label>
                        <select id="sort-select" class="sort-select">
                            <option value="nom-asc" <?php echo ($orderBy == 'nom' && $orderDir == 'ASC') ? 'selected' : ''; ?>>Nom (A-Z)</option>
                            <option value="nom-desc" <?php echo ($orderBy == 'nom' && $orderDir == 'DESC') ? 'selected' : ''; ?>>Nom (Z-A)</option>
                            <option value="coefficient-asc" <?php echo ($orderBy == 'coefficient' && $orderDir == 'ASC') ? 'selected' : ''; ?>>Coefficient (croissant)</option>
                            <option value="coefficient-desc" <?php echo ($orderBy == 'coefficient' && $orderDir == 'DESC') ? 'selected' : ''; ?>>Coefficient (décroissant)</option>
                            <option value="created_at-desc" <?php echo ($orderBy == 'created_at' && $orderDir == 'DESC') ? 'selected' : ''; ?>>Plus récent</option>
                            <option value="created_at-asc" <?php echo ($orderBy == 'created_at' && $orderDir == 'ASC') ? 'selected' : ''; ?>>Plus ancien</option>
                        </select>
                    </div>
                    <div class="view-options">
                        <button id="view-grid" class="view-btn active" title="Vue en grille">
                            <i class="fas fa-th"></i>
                        </button>
                        <button id="view-list" class="view-btn" title="Vue en liste">
                            <i class="fas fa-list"></i>
                        </button>
                        <button id="view-categories" class="view-btn" title="Vue par catégories">
                            <i class="fas fa-layer-group"></i>
                        </button>
                    </div>
                </div>

                <div class="data-table-container">
                    <!-- Vue en grille (par défaut) -->
                    <div class="criteres-grid view-mode active" id="grid-view">
                        <?php foreach ($criteres as $critere): ?>
                            <div class="critere-card animate__animated animate__fadeIn" data-critere-id="<?php echo $critere['id']; ?>" data-nom="<?php echo htmlspecialchars($critere['nom']); ?>" data-coefficient="<?php echo $critere['coefficient']; ?>">
                                <div class="critere-header">
                                    <div class="critere-info">
                                        <h3><?php echo htmlspecialchars($critere['nom']); ?></h3>
                                        <span class="critere-coefficient">
                                            Coefficient: <?php echo $critere['coefficient']; ?>
                                        </span>
                                    </div>
                                    <div class="critere-actions">
                                        <button class="btn-icon edit-critere" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon delete-critere" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="critere-details">
                                    <p><?php echo htmlspecialchars($critere['description']); ?></p>
                                    <div class="critere-meta">
                                        <span class="critere-date">Créé le: <?php echo date('d/m/Y', strtotime($critere['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Vue en liste -->
                    <div class="criteres-list view-mode" id="list-view">
                        <table class="criteres-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Coefficient</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criteres as $critere): ?>
                                <tr data-critere-id="<?php echo $critere['id']; ?>" data-nom="<?php echo htmlspecialchars($critere['nom']); ?>" data-coefficient="<?php echo $critere['coefficient']; ?>">
                                    <td><?php echo htmlspecialchars($critere['nom']); ?></td>
                                    <td class="description-cell"><?php echo htmlspecialchars(substr($critere['description'], 0, 100)) . (strlen($critere['description']) > 100 ? '...' : ''); ?></td>
                                    <td><?php echo $critere['coefficient']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($critere['created_at'])); ?></td>
                                    <td>
                                        <button class="btn-icon edit-critere" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon delete-critere" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vue par catégories -->
                    <div class="criteres-categories view-mode" id="categories-view">
                        <?php foreach ($categories as $category => $categoryItems): ?>
                        <div class="category-section">
                            <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                            <div class="category-items">
                                <?php foreach ($categoryItems as $critere): ?>
                                <div class="critere-card" data-critere-id="<?php echo $critere['id']; ?>" data-nom="<?php echo htmlspecialchars($critere['nom']); ?>" data-coefficient="<?php echo $critere['coefficient']; ?>">
                                    <div class="critere-header">
                                        <div class="critere-info">
                                            <h3><?php echo htmlspecialchars($critere['nom']); ?></h3>
                                            <span class="critere-coefficient">
                                                Coefficient: <?php echo $critere['coefficient']; ?>
                                            </span>
                                        </div>
                                        <div class="critere-actions">
                                            <button class="btn-icon edit-critere" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon delete-critere" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="critere-details">
                                        <p><?php echo htmlspecialchars($critere['description']); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier un critère -->
    <div id="critere-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un critère</h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="critere-form" novalidate>
                    <input type="hidden" id="critereId" name="critereId">
                    
                    <div class="form-group">
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie">
                            <option value="">Sélectionner une catégorie</option>
                            <option value="Innovation">Innovation</option>
                            <option value="Faisabilité">Faisabilité</option>
                            <option value="Impact">Impact</option>
                            <option value="Durabilité">Durabilité</option>
                            <option value="Présentation">Présentation</option>
                            <option value="Autre">Autre</option>
                        </select>
                        <div class="form-text">Regroupez les critères par catégorie pour une meilleure organisation</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nom">Nom du critère <span class="required">*</span></label>
                        <input type="text" id="nom" name="nom" required minlength="3">
                        <div class="validation-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" rows="4" required minlength="10"></textarea>
                        <div class="validation-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="coefficient">Coefficient <span class="required">*</span></label>
                        <input type="number" id="coefficient" name="coefficient" min="1" max="5" step="0.5" required>
                        <div class="form-text">Valeur entre 1 et 5 qui définit l'importance du critère</div>
                        <div class="validation-feedback"></div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        const addCritereBtn = document.getElementById('add-critere-btn');
        const critereForm = document.getElementById('critere-form');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancel-btn');
        const searchInput = document.getElementById('search-critere');
        const sortSelect = document.getElementById('sort-select');
        const viewButtons = document.querySelectorAll('.view-btn');
        const viewModes = document.querySelectorAll('.view-mode');
        let isLoading = false;
        
        // Fonction pour changer de vue
        function changeView(viewId) {
            // Désactiver tous les boutons et cacher toutes les vues
            viewButtons.forEach(btn => btn.classList.remove('active'));
            viewModes.forEach(view => view.classList.remove('active'));
            
            // Activer le bouton et la vue sélectionnés
            const targetView = viewId.replace('view-', '') + '-view';
            document.getElementById(targetView).classList.add('active');
            document.getElementById(viewId).classList.add('active');
            
            // Sauvegarder la préférence dans localStorage
            localStorage.setItem('criteres-view-mode', viewId);
        
            // Appliquer les filtres après le changement de vue
            setTimeout(() => {
                applyFilters();
            }, 0);
        }
        
        // Gestionnaires d'événements
        document.addEventListener('DOMContentLoaded', function() {
            // Restaurer la vue préférée de l'utilisateur
            const savedViewMode = localStorage.getItem('criteres-view-mode');
            if (savedViewMode) {
                changeView(savedViewMode);
            }
            
            // Bouton d'ajout
            if (addCritereBtn) {
                addCritereBtn.addEventListener('click', () => showModal());
            }
        
            // Boutons de fermeture du modal
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', hideModal);
            }
            if (cancelBtn) {
                cancelBtn.addEventListener('click', hideModal);
            }
        
            // Recherche
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
            
            // Tri
            if (sortSelect) {
                sortSelect.addEventListener('change', async () => {
                    await refreshCriteres();
                });
            }
            
            // Changement de vue
            viewButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const viewId = btn.id;
                    changeView(viewId);
                });
            });
        
            // Gestion du menu mobile
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        
            // Attacher les événements initialement
            attachCritereActions();
        });
        
        // Fonction pour attacher les événements aux cartes
        function attachCritereActions() {
            // Édition - pour toutes les vues
            document.querySelectorAll('.edit-critere').forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Trouver l'élément parent (carte ou ligne) avec l'ID du critère
                    const parent = this.closest('[data-critere-id]');
                    
                    // Vérifier si le parent et l'ID du critère existent
                    if (!parent || !parent.dataset.critereId) {
                         console.warn('Edit button found without valid parent critere element', this);
                         return; // Ignorer ce bouton s'il n'est pas associé à un critère valide
                    }
                    
                    const critereId = parent.dataset.critereId;

                    try {
                        setLoading(true);
                        const response = await fetch(`actions/get_critere.php?id=${critereId}`);
                        const data = await response.json();
                        
                        // Vérifier si les données du critère sont valides
                        if (data.success && data.critere) {
                            showModal(data.critere); // Appeler showModal avec les données du critère
                        } else {
                            showNotification(data.message || 'Erreur lors de la récupération du critère', 'error');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        showNotification('Une erreur est survenue lors de la récupération du critère', 'error');
                    } finally {
                        setLoading(false);
                    }
                });
            });

            // Suppression - pour toutes les vues
            document.querySelectorAll('.delete-critere').forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Trouver l'élément parent (carte ou ligne) avec l'ID du critère
                    const parent = this.closest('[data-critere-id]');

                    // Vérifier si le parent et l'ID/Nom du critère existent
                    if (!parent || !parent.dataset.critereId || !parent.dataset.nom) {
                         console.warn('Delete button found without valid parent critere element or name', this);
                         return; // Ignorer ce bouton s'il n'est pas associé à un critère valide
                    }
                    
                    const critereId = parent.dataset.critereId;
                    const critereName = parent.dataset.nom;

                    if (!confirm(`Êtes-vous sûr de vouloir supprimer le critère "${critereName}" ?`)) {
                        return;
                    }

                    try {
                        setLoading(true);
                        const response = await fetch('actions/delete_critere.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: critereId })
                        });

                        const data = await response.json();

                        if (data.success) {
                            showNotification(data.message);
                            // Rafraîchir toute la liste pour mettre à jour toutes les vues
                            await refreshCriteres();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        showNotification('Une erreur est survenue lors de la suppression du critère', 'error');
                    } finally {
                        setLoading(false);
                    }
                });
            });
        }
        
        // Fonction pour filtrer les critères
        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeViewElement = document.querySelector('.view-mode.active');
            
            // Vérifier si la vue active est trouvée
            if (!activeViewElement) {
                console.error('Aucune vue active trouvée');
                return;
            }

            const activeViewId = activeViewElement.id;
            
            // Filtrer selon la vue active
            if (activeViewId === 'grid-view' || activeViewId === 'categories-view') {
                const cards = activeViewElement.querySelectorAll('.critere-card');
                cards.forEach(card => {
                    // Vérifier les données nécessaires
                    const nomElement = card.dataset.nom;
                    const descriptionElement = card.querySelector('.critere-details p');

                    if (!nomElement || !descriptionElement) {
                        card.style.display = 'none';
                        console.warn('Élément de carte invalide ignoré', card);
                        return;
                    }

                    const nom = nomElement.toLowerCase();
                    const description = descriptionElement.textContent.toLowerCase();
                    const isVisible = nom.includes(searchTerm) || description.includes(searchTerm);
                    card.style.display = isVisible ? '' : 'none';
                });
                
                // Pour la vue par catégories, masquer les sections vides
                if (activeViewId === 'categories-view') {
                    const sections = activeViewElement.querySelectorAll('.category-section');
                    sections.forEach(section => {
                        const visibleCards = section.querySelectorAll('.critere-card:not([style="display: none;"])');
                        section.style.display = visibleCards.length === 0 ? 'none' : '';
                    });
                }
            } else if (activeViewId === 'list-view') {
                const rows = activeViewElement.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    // Vérifier les données nécessaires
                    const nomElement = row.dataset.nom;
                    const descriptionElement = row.querySelector('.description-cell');

                    if (!nomElement || !descriptionElement) {
                        row.style.display = 'none';
                        console.warn('Élément de ligne invalide ignoré', row);
                        return;
                    }

                    const nom = nomElement.toLowerCase();
                    const description = descriptionElement.textContent.toLowerCase();
                    const isVisible = nom.includes(searchTerm) || description.includes(searchTerm);
                    row.style.display = isVisible ? '' : 'none';
                });
            }
        }

        // Fonction pour rafraîchir la liste des critères
        async function refreshCriteres() {
            try {
                // Récupérer les paramètres de tri actuels
                const sortValue = sortSelect.value;
                const [sortField, sortDir] = sortValue.split('-');
                
                const response = await fetch(`criteres.php?sort=${sortField}&dir=${sortDir}`);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Mettre à jour chaque vue
                const newGridView = doc.querySelector('#grid-view');
                const newListView = doc.querySelector('#list-view');
                const newCategoriesView = doc.querySelector('#categories-view');
                
                if (newGridView) {
                    document.getElementById('grid-view').innerHTML = newGridView.innerHTML;
                }
                
                if (newListView) {
                    document.getElementById('list-view').innerHTML = newListView.innerHTML;
                }
                
                if (newCategoriesView) {
                    document.getElementById('categories-view').innerHTML = newCategoriesView.innerHTML;
                }
                
                // S'assurer que le DOM est mis à jour avant de réattacher les événements
                requestAnimationFrame(() => {
                    attachCritereActions();
                    applyFilters();
                });
            } catch (error) {
                console.error('Erreur lors du rafraîchissement:', error);
                showNotification('Erreur lors du rafraîchissement de la liste', 'error');
            }
        }

        // Fonction pour afficher/masquer le chargement
        function setLoading(loading) {
            isLoading = loading;
            const submitBtn = critereForm.querySelector('button[type="submit"]');
            if (loading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Enregistrer';
            }
        }

        // Fonction pour afficher une notification
        function showNotification(message, type = 'success') {
            // Supprimer les notifications existantes
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());
            
            // Créer et afficher la nouvelle notification
            const notification = document.createElement('div');
            notification.className = `notification ${type} animate__animated animate__fadeInRight`;
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            // Ajouter une classe pour l'animation de sortie après un délai
            setTimeout(() => {
                notification.classList.remove('animate__fadeInRight');
                notification.classList.add('animate__fadeOutRight');
                
                // Supprimer l'élément après l'animation
                notification.addEventListener('animationend', () => {
                    notification.remove();
                });
            }, 3000);
        }

        // Fonction pour valider les données
        function validateCritereData(data) {
            const errors = [];
            
            // Validation du nom
            if (!data.nom.trim()) {
                errors.push('Le nom est requis');
            } else if (data.nom.trim().length < 3) {
                errors.push('Le nom doit contenir au moins 3 caractères');
            }

            // Validation de la description
            if (!data.description.trim()) {
                errors.push('La description est requise');
            } else if (data.description.trim().length < 10) {
                errors.push('La description doit contenir au moins 10 caractères');
            }

            // Validation du coefficient
            if (!data.coefficient || data.coefficient < 1 || data.coefficient > 5) {
                errors.push('Le coefficient doit être entre 1 et 5');
            }

            return errors;
        }

        // Fonction pour afficher le modal
        function showModal(critere = null) {
            const modal = document.getElementById('critere-modal');
            if (!modal) return;

            if (critere) {
                document.getElementById('critereId').value = critere.id;
                document.getElementById('nom').value = critere.nom;
                document.getElementById('description').value = critere.description;
                document.getElementById('coefficient').value = critere.coefficient;
                document.querySelector('.modal-header h3').textContent = 'Modifier un critère';
            } else {
                critereForm.reset();
                document.getElementById('critereId').value = '';
                document.querySelector('.modal-header h3').textContent = 'Ajouter un critère';
            }

            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.classList.add('show');
            });
        }

        // Fonction pour masquer le modal
        function hideModal() {
            const modal = document.getElementById('critere-modal');
            if (!modal) return;

            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                critereForm.reset();
            }, 300);
        }
    </script>
</body>
</html>



// Fonction pour filtrer les critères
function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const activeViewElement = document.querySelector('.view-mode.active');
    
    // Vérifier si la vue active est trouvée
    if (!activeViewElement) {
        console.error('Aucune vue active trouvée');
        return;
    }

    const activeViewId = activeViewElement.id;
    
    // Filtrer selon la vue active
    if (activeViewId === 'grid-view' || activeViewId === 'categories-view') {
        const cards = activeViewElement.querySelectorAll('.critere-card');
        cards.forEach(card => {
            // Vérifier les données nécessaires
            const nomElement = card.dataset.nom;
            const descriptionElement = card.querySelector('.critere-details p');

            if (!nomElement || !descriptionElement) {
                card.style.display = 'none';
                console.warn('Élément de carte invalide ignoré', card);
                return;
            }

            const nom = nomElement.toLowerCase();
            const description = descriptionElement.textContent.toLowerCase();
            const isVisible = nom.includes(searchTerm) || description.includes(searchTerm);
            card.style.display = isVisible ? '' : 'none';
        });
        
        // Pour la vue par catégories, masquer les sections vides
        if (activeViewId === 'categories-view') {
            const sections = activeViewElement.querySelectorAll('.category-section');
            sections.forEach(section => {
                const visibleCards = section.querySelectorAll('.critere-card:not([style="display: none;"])');
                section.style.display = visibleCards.length === 0 ? 'none' : '';
            });
        }
    } else if (activeViewId === 'list-view') {
        const rows = activeViewElement.querySelectorAll('tbody tr');
        rows.forEach(row => {
            // Vérifier les données nécessaires
            const nomElement = row.dataset.nom;
            const descriptionElement = row.querySelector('.description-cell');

            if (!nomElement || !descriptionElement) {
                row.style.display = 'none';
                console.warn('Élément de ligne invalide ignoré', row);
                return;
            }

            const nom = nomElement.toLowerCase();
            const description = descriptionElement.textContent.toLowerCase();
            const isVisible = nom.includes(searchTerm) || description.includes(searchTerm);
            row.style.display = isVisible ? '' : 'none';
        });
    }
}

// Fonction pour rafraîchir la liste des critères
async function refreshCriteres() {
    try {
        // Récupérer les paramètres de tri actuels
        const sortValue = sortSelect.value;
        const [sortField, sortDir] = sortValue.split('-');
        
        const response = await fetch(`criteres.php?sort=${sortField}&dir=${sortDir}`);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Mettre à jour chaque vue
        const newGridView = doc.querySelector('#grid-view');
        const newListView = doc.querySelector('#list-view');
        const newCategoriesView = doc.querySelector('#categories-view');
        
        if (newGridView) {
            document.getElementById('grid-view').innerHTML = newGridView.innerHTML;
        }
        
        if (newListView) {
            document.getElementById('list-view').innerHTML = newListView.innerHTML;
        }
        
        if (newCategoriesView) {
            document.getElementById('categories-view').innerHTML = newCategoriesView.innerHTML;
        }
        
        // S'assurer que le DOM est mis à jour avant de réattacher les événements
        requestAnimationFrame(() => {
            attachCritereActions();
            applyFilters();
        });
    } catch (error) {
        console.error('Erreur lors du rafraîchissement:', error);
        showNotification('Erreur lors du rafraîchissement de la liste', 'error');
    }
}

// Fonction pour afficher/masquer le chargement
function setLoading(loading) {
    isLoading = loading;
    const submitBtn = critereForm.querySelector('button[type="submit"]');
    if (loading) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Enregistrer';
    }
}

// Fonction pour afficher une notification
function showNotification(message, type = 'success') {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Créer et afficher la nouvelle notification
    const notification = document.createElement('div');
    notification.className = `notification ${type} animate__animated animate__fadeInRight`;
    notification.innerHTML = message;
    document.body.appendChild(notification);
    
    // Ajouter une classe pour l'animation de sortie après un délai
    setTimeout(() => {
        notification.classList.remove('animate__fadeInRight');
        notification.classList.add('animate__fadeOutRight');
        
        // Supprimer l'élément après l'animation
        notification.addEventListener('animationend', () => {
            notification.remove();
        });
    }, 3000);
}

// Fonction pour valider les données
function validateCritereData(data) {
    const errors = [];
    
    // Validation du nom
    if (!data.nom.trim()) {
        errors.push('Le nom est requis');
    } else if (data.nom.trim().length < 3) {
        errors.push('Le nom doit contenir au moins 3 caractères');
    }

    // Validation de la description
    if (!data.description.trim()) {
        errors.push('La description est requise');
    } else if (data.description.trim().length < 10) {
        errors.push('La description doit contenir au moins 10 caractères');
    }

    // Validation du coefficient
    if (!data.coefficient || data.coefficient < 1 || data.coefficient > 5) {
        errors.push('Le coefficient doit être entre 1 et 5');
    }

    return errors;
}

// Fonction pour afficher le modal
function showModal(critere = null) {
    const modal = document.getElementById('critere-modal');
    if (!modal) return;

    if (critere) {
        document.getElementById('critereId').value = critere.id;
        document.getElementById('nom').value = critere.nom;
        document.getElementById('description').value = critere.description;
        document.getElementById('coefficient').value = critere.coefficient;
        document.querySelector('.modal-header h3').textContent = 'Modifier un critère';
    } else {
        critereForm.reset();
        document.getElementById('critereId').value = '';
        document.querySelector('.modal-header h3').textContent = 'Ajouter un critère';
    }

    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });
}

// Fonction pour masquer le modal
function hideModal() {
    const modal = document.getElementById('critere-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        critereForm.reset();
    }, 300);
}