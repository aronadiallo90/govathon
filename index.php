<?php
session_start();

// Verify user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verify user has admin privileges
if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Govathon</title>
    <link rel="stylesheet" href="styles.css">
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
                        <input type="text" placeholder="Rechercher...">
                    </div>
            <div class="user-info">
                <i class="fas fa-bell"></i>
                <div class="user-profile">
                    <img src="https://via.placeholder.com/40" alt="Profile">
                    <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
                    <p>Role: <?= htmlspecialchars($_SESSION['user_role']) ?></p>
                    <a href="logout.php" style="margin-left: 10px; color: #f00; text-decoration: none; font-weight: bold;">Se déconnecter</a>
                </div>
            </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="stats-cards">
                    <div class="card">
                        <div class="card-icon" style="background: rgba(52, 152, 219, 0.2);">
                            <i class="fas fa-project-diagram" style="color: #3498db;"></i>
                        </div>
                        <div class="card-info">
                            <h3>24</h3>
                            <p>Projets</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background: rgba(46, 204, 113, 0.2);">
                            <i class="fas fa-user-tie" style="color: #2ecc71;"></i>
                        </div>
                        <div class="card-info">
                            <h3>12</h3>
                            <p>Membres du Jury</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background: rgba(155, 89, 182, 0.2);">
                            <i class="fas fa-industry" style="color: #9b59b6;"></i>
                        </div>
                        <div class="card-info">
                            <h3>6</h3>
                            <p>Secteurs</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background: rgba(241, 196, 15, 0.2);">
                            <i class="fas fa-vote-yea" style="color: #f1c40f;"></i>
                        </div>
                        <div class="card-info">
                            <h3>156</h3>
                            <p>Votes</p>
                        </div>
                    </div>
                </div>

                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Distribution des Projets par Secteur</h3>
                        <canvas id="sectorsChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <h3>Notes Moyennes par Critère</h3>
                        <canvas id="criteriaChart"></canvas>
                    </div>
                </div>

                <div class="recent-activity">
                    <h3>Activités Récentes</h3>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div class="activity-details">
                                <p>Nouveau projet déposé : "Smart City"</p>
                                <span>Il y a 5 minutes</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <div class="activity-details">
                                <p>Nouveau vote enregistré par Jury #3</p>
                                <span>Il y a 15 minutes</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="activity-details">
                                <p>Phase de vote terminée</p>
                                <span>Il y a 30 minutes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>
