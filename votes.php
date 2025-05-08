<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Votes - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="css/votes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <input type="text" placeholder="Rechercher un vote...">
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
                    <h2>Gestion des Votes</h2>
                    <button id="add-vote-btn" class="btn-primary" onclick="showModal()">
                        <i class="fas fa-plus"></i> Ajouter un vote
                    </button>
                </div>

                <div class="data-filters">
                    <select id="projectFilter">
                        <option value="">Tous les projets</option>
                    </select>
                    <select id="juryFilter">
                        <option value="">Tous les jurys</option>
                    </select>
                    <input type="date" id="dateFilter">
                    <button class="btn-secondary">Filtrer</button>
                </div>

                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Projet</th>
                                <th>Jury</th>
                                <th>Innovation</th>
                                <th>Faisabilité</th>
                                <th>Impact</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="votes-table-body">
                            <!-- Les votes seront ajoutés ici dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier un vote -->
    <div id="vote-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un vote</h3>
                <button type="button" class="close-modal" onclick="hideModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="vote-form">
                    <input type="hidden" id="voteId" name="voteId">
                    
                    <div class="form-group">
                        <label for="projectSelect">Projet</label>
                        <select id="projectSelect" name="projectSelect" required>
                            <option value="">Sélectionner un projet</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jurySelect">Jury</label>
                        <select id="jurySelect" name="jurySelect" required>
                            <option value="">Sélectionner un jury</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="innovation">Innovation (1-10)</label>
                        <div class="vote-slider">
                            <input type="range" id="innovation" name="innovation" min="1" max="10" value="5" required>
                            <span class="value-display">5</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="feasibility">Faisabilité (1-10)</label>
                        <div class="vote-slider">
                            <input type="range" id="feasibility" name="feasibility" min="1" max="10" value="5" required>
                            <span class="value-display">5</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="impact">Impact (1-10)</label>
                        <div class="vote-slider">
                            <input type="range" id="impact" name="impact" min="1" max="10" value="5" required>
                            <span class="value-display">5</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="comments">Commentaires</label>
                        <textarea id="comments" name="comments" rows="4"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/votes.js"></script>
</body>
</html>
