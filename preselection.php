<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

// Récupération des projets en attente de présélection (statut 'pending')
$stmt = $pdo->prepare("
    SELECT p.*, s.nom as secteur_nom, 
           pdv_email.field_value as participant_email -- Récupérer l'email du participant
    FROM projects p
    JOIN secteurs s ON p.secteur_id = s.id
    LEFT JOIN project_dynamic_values pdv_email ON p.id = pdv_email.project_id
    LEFT JOIN dynamic_field_definitions dfd_email ON pdv_email.field_id = dfd_email.id AND dfd_email.field_name = 'Email'
    WHERE p.status = 'pending'
    ORDER BY p.created_at DESC
");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Présélection des Projets - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/projects.css">
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <?php include 'components/header.php'; ?>

            <div class="data-management-content">
                <div class="data-header">
                    <h2>Projets en attente de Présélection</h2>
                </div>

                <?php if (empty($projects)): ?>
                    <div class="alert alert-info">Aucun projet en attente de présélection.</div>
                <?php else: ?>
                    <div class="projects-grid">
                        <?php foreach ($projects as $project): ?>
                            <div class="project-card">
                                <div class="project-header">
                                    <h3><?php echo htmlspecialchars($project['nom']); ?></h3>
                                    <span class="status-badge pending">
                                        En attente
                                    </span>
                                </div>

                                <div class="project-info">
                                    <p><strong>Secteur:</strong> <?php echo htmlspecialchars($project['secteur_nom']); ?></p>
                                    <p><strong>Email Participant:</strong> <?php echo htmlspecialchars($project['participant_email'] ?? 'N/A'); ?></p>
                                    <p><strong>Date de soumission:</strong> <?php echo date('d/m/Y', strtotime($project['created_at'])); ?></p>
                                </div>

                                <div class="project-description">
                                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                                </div>

                                <div class="project-actions">
                                    <form method="POST" action="actions/update_project_status.php" class="preselection-form">
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <input type="hidden" name="current_status" value="pending">
                                        
                                        <div class="form-group">
                                            <label for="notes_<?php echo $project['id']; ?>">Notes de présélection (optionnel)</label>
                                            <textarea id="notes_<?php echo $project['id']; ?>" 
                                                      name="preselection_notes" 
                                                      rows="3"></textarea>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" name="action" value="approve" class="btn-success">
                                                Approuver
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn-danger">
                                                Rejeter
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 