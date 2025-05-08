<?php
session_start();
require_once 'includes/db.php';

// Vérifier les droits d'accès (même logique que sectors.php)
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

// Récupérer le nom de l'utilisateur connecté pour afficher les initiales
$userName = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $userName = $user['name'];
    }
}

// Fonction pour obtenir les initiales
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= mb_substr($word, 0, 1);
    }
    return mb_strtoupper($initials);
}
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
    <link rel="stylesheet" href="css/jury.css">
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
                            <div class="jury-avatar president"><?= htmlspecialchars(getInitials($userName)) ?></div>
                            <span><?= htmlspecialchars($userName) ?></span>
                        </div>
                    </div>
                </div>
            </header>
            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Jurys</h2>
                    <button id="add-jury-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un jury
                    </button>
                </div>

                <div class="data-filters">
                    <select id="filter-role">
                        <option value="all">Tous les rôles</option>
                        <option value="president">Président</option>
                        <option value="member">Membre</option>
                        <option value="expert">Expert</option>
                    </select>
                    <select id="filter-status">
                        <option value="all">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                    <button class="btn-secondary">Filtrer</button>
                </div>

                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jury</th>
                                <th>Rôle</th>
                                <th>Email</th>
                                <th>Projets évalués</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jury-table-body">
                            <!-- Les jurys seront chargés dynamiquement via JavaScript -->
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

     <!-- Modal pour ajouter/modifier un jury -->
     <div id="jury-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un jury</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="jury-form">
                    <input type="hidden" id="jury-id">
                    <div class="form-group">
                        <label for="jury-name">Nom complet</label>
                        <input type="text" id="jury-name" required>
                    </div>
                    <div class="form-group">
                        <label for="jury-email">Email</label>
                        <input type="email" id="jury-email" required>
                    </div>
                    <div class="form-group">
                        <label for="jury-password">Mot de passe</label>
                        <input type="password" id="jury-password">
                        <small>Laissez vide pour conserver l'ancien mot de passe</small>
                    </div>
                    <div class="form-group">
                        <label for="jury-secteur">Secteur</label>
                        <select id="jury-secteur">
                            <option value="">Sélectionner un secteur</option>
                            <?php foreach ($secteurs as $secteur): ?>
                                <option value="<?= $secteur['id'] ?>"><?= htmlspecialchars($secteur['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="jury-is-global">
                            Jury global
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="jury-status">Statut</label>
                        <select id="jury-status">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/jury.js"></script>
</body>
</html>
