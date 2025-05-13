<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: unauthorized.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nom, description, icon, created_at, updated_at FROM secteurs ORDER BY nom ASC");
    $stmt->execute();
    $sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sectors = [];
}

$userName = $_SESSION['user_name'] ?? 'Utilisateur';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Secteurs - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="css/data-management.css" />
    <link rel="stylesheet" href="css/sectors.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un secteur..." />
                    </div>
                    <div class="user-info">
                        <i class="fas fa-bell"></i>
                        <div class="user-profile">
                            <div class="jury-avatar president"><?= htmlspecialchars(getInitials($userName)) ?></div>
                            <span><?= htmlspecialchars($userName) ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Secteurs</h2>
                    <button id="add-sector-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un secteur
                    </button>
                </div>

                <div class="data-filters">
                    <select id="filter-type">
                        <option value="all">Tous les types</option>
                        <option value="technology">Technologie</option>
                        <option value="health">Santé</option>
                        <option value="education">Éducation</option>
                        <option value="environment">Environnement</option>
                    </select>
                    <button class="btn-secondary">Filtrer</button>
                </div>

                <div class="sectors-grid">
                    <?php foreach ($sectors as $sector): ?>
                        <div class="sector-card" data-id="<?= htmlspecialchars($sector['id']) ?>">
                            <div class="sector-header">
                                <i class="fas <?= htmlspecialchars($sector['icon'] ?? 'fa-building') ?> sector-icon"></i>
                                <h3><?= htmlspecialchars($sector['nom']) ?></h3>
                                <div class="sector-actions">
                                    <button class="btn-icon edit-btn" onclick="editSector(<?= $sector['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete-btn" onclick="deleteSector(<?= $sector['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="sector-description"><?= htmlspecialchars($sector['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier un secteur -->
    <div id="sectorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un secteur</h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="sectorForm">
                    <div class="form-group">
                        <label for="sectorName">Nom du secteur</label>
                        <input type="text" id="sectorName" required />
                    </div>
                    <div class="form-group">
                        <label for="sectorDescription">Description</label>
                        <textarea id="sectorDescription" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Choisir une icône</label>
                        <div class="icon-selector">
                            <div class="icon-option selected" data-icon="fa-laptop">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-building">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-graduation-cap">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-heartbeat">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-industry">
                                <i class="fas fa-industry"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-leaf">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-coins">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-flask">
                                <i class="fas fa-flask"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-hammer">
                                <i class="fas fa-hammer"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-bus">
                                <i class="fas fa-bus"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-chart-line">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="icon-option" data-icon="fa-network-wired">
                                <i class="fas fa-network-wired"></i>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="cancelBtn" class="btn-secondary">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/sectors.js"></script>
</body>
</html>
