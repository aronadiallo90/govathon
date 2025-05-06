<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Critères - GOVATHON</title>
    
     
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/criteria.css">
    
    
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <button id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un projet...">
                    </div>
                    <div class="user-info">
                        <i class="fas fa-bell"></i>
                        <div class="user-profile">
                            <img src="https://via.placeholder.com/40" alt="Profile">
                            <span>Admin</span>
                        </div>
                    </div>
                </div>
            </header>
            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Critères</h2>
                    <button class="btn-primary" onclick="openModal()">
                        <i class="fas fa-plus"></i> Ajouter un critère
                    </button>
                </div>

                <div class="criteria-filters">
                    <select id="sectorFilter">
                        <option value="">Tous les secteurs</option>
                        <option value="tech">Technologie</option>
                        <option value="health">Santé</option>
                        <option value="education">Éducation</option>
                    </select>
                    <select id="stageFilter">
                        <option value="">Toutes les étapes</option>
                        <option value="qualification">Qualification</option>
                        <option value="finale">Finale</option>
                    </select>
                </div>

                <div class="criteria-list">
                    <div class="criteria-group">
                        <div class="group-header">
                            <h3>Innovation et Créativité</h3>
                            <span class="weight">Poids: 30%</span>
                        </div>
                        <div class="criteria-items">
                            <div class="criteria-item">
                                <div class="criteria-info">
                                    <h4>Originalité de la solution</h4>
                                    <p>Évaluation de l'unicité et de la créativité de la solution proposée</p>
                                </div>
                                <div class="criteria-actions">
                                    <button class="btn-icon edit-btn" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete-btn" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Autres critères... -->
                        </div>
                    </div>
                    <!-- Autres groupes... -->
                </div>
            </div>
           
        </main>
    </div>

    <div class="modal" id="criteriaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un critère</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="criteriaForm">
                    <div class="form-group">
                        <label for="criteriaName">Nom du critère</label>
                        <input type="text" id="criteriaName" required>
                    </div>
                    <div class="form-group">
                        <label for="criteriaDescription">Description</label>
                        <textarea id="criteriaDescription" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="criteriaWeight">Poids (%)</label>
                        <input type="number" id="criteriaWeight" min="0" max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="criteriaSector">Secteur</label>
                        <select id="criteriaSector" required>
                            <option value="">Sélectionner un secteur</option>
                            <option value="tech">Technologie</option>
                            <option value="health">Santé</option>
                            <option value="education">Éducation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="criteriaStage">Étape</label>
                        <select id="criteriaStage" required>
                            <option value="">Sélectionner une étape</option>
                            <option value="qualification">Qualification</option>
                            <option value="finale">Finale</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/criteria.js"></script>
</body>
</html>
