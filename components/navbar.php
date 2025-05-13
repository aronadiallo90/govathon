<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

// Récupérer les champs dynamiques pour superadmin
$dynamicFields = [];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin') {
    try {
        $stmt = $pdo->query('SELECT field_name FROM dynamic_fields ORDER BY id ASC');
        $dynamicFields = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
?>
<nav class="sidebar" style="overflow-y: auto; max-height: 100vh;">
    <div class="logo">
        <i class="fas fa-code"></i>
        <span>GOVATHON</span>
    </div>
    <ul class="nav-links">
        <li>
            <a href="index.php">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        
        <!-- Gestion des projets -->
        <li class="nav-section">
            <span class="nav-section-title">Gestion des Projets</span>
        </li>
        <li>
            <a href="projects.php">
                <i class="fas fa-project-diagram"></i>
                <span>Projets</span>
            </a>
        </li>
        <li>
            <a href="criteres.php">
                <i class="fas fa-list-check"></i>
                <span>Critères</span>
            </a>
        </li>
        <li>
            <a href="votes.php">
                <i class="fas fa-vote-yea"></i>
                <span>Votes</span>
            </a>
        </li>
        <li>
            <a href="etapes.php">
                <i class="fas fa-tasks"></i>
                <span>Étapes</span>
            </a>
        </li>

        <?php if (isAdmin()): ?>
        <!-- Administration -->
        <li class="nav-section">
            <span class="nav-section-title">Administration</span>
        </li>
        <li>
            <a href="sectors.php">
                <i class="fas fa-building"></i>
                <span>Secteurs</span>
            </a>
        </li>
        <li>
            <a href="jury.php">
                <i class="fas fa-user-tie"></i>
                <span>Jury</span>
            </a>
        </li>
        <li>
            <a href="users.php">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (isSuperAdmin()): ?>
        <!-- Configuration avancée -->
        <li class="nav-section">
            <span class="nav-section-title">Configuration</span>
        </li>
        <li>
            <a href="project_dynamic_fields.php">
                <i class="fas fa-puzzle-piece"></i>
                <span>Champs Dynamiques</span>
            </a>
        </li>
        <li>
            <a href="user_permissions.php">
                <i class="fas fa-user-shield"></i>
                <span>Gestion des Droits</span>
            </a>
        </li>
        <li>
            <a href="settings.php">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
