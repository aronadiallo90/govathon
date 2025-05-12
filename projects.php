<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Define getStatusLabel function first
function getStatusLabel($status) {
    $statusLabels = [
        'draft' => 'Brouillon',
        'submitted' => 'Soumis',
        'under_review' => "En cours d'évaluation",
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté'
    ];
    return $statusLabels[$status] ?? 'Brouillon';
}

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: unauthorized.php');
    exit;
}

// Récupérer les secteurs
try {
    $stmt = $pdo->query("SELECT id, nom FROM secteurs ORDER BY nom ASC");
    $secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $secteurs = [];
}

// Récupérer les champs dynamiques
try {
    $stmt = $pdo->query("SELECT * FROM dynamic_field_definitions ORDER BY id ASC");
    $dynamicFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dynamicFields = [];
}

$userName = $_SESSION['user_name'] ?? 'Utilisateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Projets - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/projects.css">
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
                        <?php include 'components/header.php'; ?>


            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Projets</h2>
                    <button id="add-project-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un projet
                    </button>
                </div>

                <div class="data-filters">
                    <select id="filter-sector">
                        <option value="all">Tous les secteurs</option>
                        <?php foreach ($secteurs as $secteur): ?>
                            <option value="<?= htmlspecialchars($secteur['id']) ?>">
                                <?= htmlspecialchars($secteur['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filter-status">
                        <option value="all">Tous les statuts</option>
                        <option value="draft">Brouillon</option>
                        <option value="submitted">Soumis</option>
                        <option value="under_review">En cours d'évaluation</option>
                        <option value="approved">Approuvé</option>
                        <option value="rejected">Rejeté</option>
                    </select>
                    <button class="btn-secondary">Filtrer</button>
                </div>

                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>User Email</th>
                                <th>Secteur</th>
                                <th>Date de soumission</th>
                                <th>Statut</th>
                                <th>Note moyenne</th>
                                <?php foreach ($dynamicFields as $field): ?>
                                    <th><?= htmlspecialchars($field['field_name']) ?></th>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="projects-table-body">
                            <!-- Les projets seront chargés dynamiquement via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <button class="btn-secondary"><i class="fas fa-chevron-left"></i></button>
                    <span>Page 1 sur 3</span>
                    <button class="btn-secondary"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier un projet -->
    <div id="project-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un projet</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="project-form">
                    <input type="hidden" id="project-id">
                    <div class="form-group">
                        <label for="project-name">Nom du projet</label>
                        <input type="text" id="project-name" required>
                    </div>
                    <div class="form-group">
                        <label for="project-description">Description</label>
                        <textarea id="project-description" required rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="project-sector">Secteur</label>
                        <select id="project-sector" required>
                            <option value="">Sélectionner un secteur</option>
                            <?php foreach ($secteurs as $secteur): ?>
                                <option value="<?= $secteur['id'] ?>"><?= htmlspecialchars($secteur['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Removed status field as per request -->
                    <div class="form-group">
                        <h4>Champs additionnels</h4>
                        <div id="dynamic-fields">
                            <?php foreach ($dynamicFields as $field): ?>
                                <div class="form-group">
                                    <label for="dynamic-<?= htmlspecialchars($field['id']) ?>">
                                        <?= htmlspecialchars($field['field_name']) ?>
                                    </label>
                                    <?php if ($field['field_type'] === 'textarea'): ?>
                                        <textarea 
                                            id="dynamic-<?= htmlspecialchars($field['id']) ?>"
                                            name="dynamic_fields[<?= htmlspecialchars($field['id']) ?>]"
                                            <?= $field['is_required'] ? 'required' : '' ?>
                                            rows="4"
                                        ></textarea>
                                    <?php else: ?>
                                        <input 
                                            type="<?= htmlspecialchars($field['field_type']) ?>"
                                            id="dynamic-<?= htmlspecialchars($field['id']) ?>"
                                            name="dynamic_fields[<?= htmlspecialchars($field['id']) ?>]"
                                            <?= $field['is_required'] ? 'required' : '' ?>
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/projects.js"></script>
</body>
</html>
