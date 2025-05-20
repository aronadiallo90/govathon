<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}

// Récupération des étapes
$stmt = $pdo->query("SELECT * FROM etapes ORDER BY ordre ASC");
$etapes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Déterminer l'étape active
$current_etape = isset($_GET['etape']) ? (int)$_GET['etape'] : null;
if (!$current_etape) {
    // Trouver l'étape en cours
    $now = new DateTime();
    foreach ($etapes as $etape) {
        $debut = new DateTime($etape['date_debut']);
        $fin = new DateTime($etape['date_fin']);
        if ($now >= $debut && $now <= $fin) {
            $current_etape = $etape['id'];
            break;
        }
    }
    // Si aucune étape n'est en cours, prendre la première
    if (!$current_etape) {
        $current_etape = $etapes[0]['id'];
    }
}

// Récupération des projets pour l'étape actuelle
$stmt = $pdo->prepare("
    SELECT DISTINCT p.*, 
           s.nom as secteur_nom, 
           s.icon as secteur_icon,
           pe.status as etape_status,
           (SELECT COUNT(DISTINCT v.id) 
            FROM votes v 
            WHERE v.project_id = p.id 
            AND v.etape_id = ?) as vote_count,
           (SELECT AVG(v.note) 
            FROM votes v 
            WHERE v.project_id = p.id 
            AND v.etape_id = ?) as avg_score
    FROM projects p
    LEFT JOIN secteurs s ON p.secteur_id = s.id
    INNER JOIN project_etapes pe ON p.id = pe.project_id AND pe.etape_id = ?
    ORDER BY pe.created_at DESC
");
$stmt->execute([$current_etape, $current_etape, $current_etape]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des projets non assignés à l'étape
$stmt = $pdo->prepare("
    SELECT DISTINCT p.*, s.nom as secteur_nom, s.icon as secteur_icon
    FROM projects p
    LEFT JOIN secteurs s ON p.secteur_id = s.id
    WHERE NOT EXISTS (
        SELECT 1 
        FROM project_etapes pe 
        WHERE pe.project_id = p.id 
        AND pe.etape_id = ?
    )
    ORDER BY p.created_at DESC
");
$stmt->execute([$current_etape]);
$unassigned_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets par Étape - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/projects_by_etape.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <?php include 'components/header.php'; ?>

            <div class="data-management-content">
                <div class="data-header">
                    <h2>Projets par Étape</h2>
                </div>

                <div class="etapes-timeline">
                    <?php 
                    $now = new DateTime();
                    foreach ($etapes as $etape): 
                        $debut = new DateTime($etape['date_debut']);
                        $fin = new DateTime($etape['date_fin']);
                        $status = '';
                        if ($etape['id'] == $current_etape) {
                            $status = 'active';
                        } elseif ($now >= $debut && $now <= $fin) {
                            $status = 'current';
                        } elseif ($now > $fin) {
                            $status = 'completed';
                        } else {
                            $status = 'future';
                        }
                    ?>
                        <div class="etape-item <?php echo $status; ?>" onclick="window.location.href='?etape=<?php echo $etape['id']; ?>'">
                            <div class="etape-dot"></div>
                            <div class="etape-content">
                                <div class="etape-label"><?php echo htmlspecialchars($etape['nom']); ?></div>
                                <div class="etape-date">
                                    <?php echo date('d/m/Y', strtotime($etape['date_debut'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($etape['date_fin'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                    <div class="unassigned-projects">
                        <h3>Projets non assignés à cette étape</h3>
                        <div class="projects-grid">
                            <?php foreach ($unassigned_projects as $project): ?>
                                <div class="project-card unassigned">
                                    <div class="project-header">
                                        <h3><?php echo htmlspecialchars($project['nom']); ?></h3>
                                    </div>
                                    <div class="project-details">
                                        <p class="project-description">
                                            <?php echo htmlspecialchars($project['description']); ?>
                                        </p>
                                        <div class="project-meta">
                                            <div class="meta-item">
                                                <i class="fas <?php echo $project['secteur_icon'] ?? 'fa-building'; ?>"></i>
                                                <span class="sector-badge">
                                                    <?php echo htmlspecialchars($project['secteur_nom']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="project-actions">
                                        <button class="btn-icon add-btn" title="Ajouter à l'étape" onclick="addProjectToEtape(<?php echo $project['id']; ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="projects-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card">
                            <div class="project-header">
                                <h3><?php echo htmlspecialchars($project['nom']); ?></h3>
                                <span class="project-status <?php echo $project['etape_status'] ?? 'non_assigne'; ?>">
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                        switch($project['etape_status']) {
                                            case 'en_cours':
                                                echo 'En cours';
                                                break;
                                            case 'valide':
                                                echo 'Validé';
                                                break;
                                            case 'elimine':
                                                echo 'Éliminé';
                                                break;
                                            default:
                                                echo 'Non assigné';
                                        }
                                    ?>
                                </span>
                            </div>

                            <div class="project-details">
                                <p class="project-description">
                                    <?php echo htmlspecialchars($project['description']); ?>
                                </p>

                                <div class="project-meta">
                                    <div class="meta-item">
                                        <i class="fas <?php echo $project['secteur_icon'] ?? 'fa-building'; ?>"></i>
                                        <span class="sector-badge">
                                            <?php echo htmlspecialchars($project['secteur_nom']); ?>
                                        </span>
                                    </div>

                                    <div class="meta-item">
                                        <i class="fas fa-vote-yea"></i>
                                        <span class="votes-count">
                                            Votes: <?php echo $project['vote_count']; ?>
                                        </span>
                                    </div>

                                    <?php if ($project['avg_score'] > 0): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-star"></i>
                                            <span class="score">
                                                Score: <?php echo number_format($project['avg_score'], 2); ?>/10
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="project-actions">
                                <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                                    <button class="btn-icon remove-btn" title="Retirer de l'étape" onclick="removeProjectFromEtape(<?php echo $project['id']; ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function addProjectToEtape(projectId) {
            if (!confirm('Voulez-vous ajouter ce projet à l\'étape actuelle ?')) return;

            const etapeId = <?php echo $current_etape; ?>;
            fetch('actions/update_project_etape.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    project_id: projectId,
                    etape_id: etapeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'ajout du projet');
            });
        }

        function removeProjectFromEtape(projectId) {
            if (!confirm('Êtes-vous sûr de vouloir retirer ce projet de cette étape ?')) return;

            const etapeId = <?php echo $current_etape; ?>;
            fetch('actions/update_project_etape.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    project_id: projectId,
                    etape_id: etapeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors du retrait du projet');
            });
        }
    </script>
</body>
</html>