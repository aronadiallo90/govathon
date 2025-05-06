<?php
session_start();
require_once 'includes/db.php';

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

// Vérifier les droits d'accès (même logique que sectors.php)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: unauthorized.php');
    exit;
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
                            <img src="https://via.placeholder.com/40" alt="Profile">
                            <span>Admin</span>
                        </div>
                    </div>
                </div>
            </header>
            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Jurys</h2>
                    <button id="add-jury-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un projet
                    </button>
                </div>

                <div class="data-filters">
                    <div class="filters-container">
                        <select id="filter-sector" class="filter-select">
                            <option value="all">Tous les secteurs</option>
                            <?php foreach ($secteurs as $secteur): ?>
                                <option value="<?= htmlspecialchars($secteur['id']) ?>">
                                    <?= htmlspecialchars($secteur['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter-status" class="filter-select">
                            <option value="all">Tous les statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="submitted">Soumis</option>
                            <option value="under_review">En cours d'évaluation</option>
                            <option value="approved">Approuvé</option>
                            <option value="rejected">Rejeté</option>
                        </select>
                        <button class="btn-secondary">Filtrer</button>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Équipe</th>
                                <th>Secteur</th>
                                <th>Date de soumission</th>
                                <th>Statut</th>
                                <th>Note moyenne</th>
<?php
require_once 'config.php';
$dynamicFieldsStmt = $pdo->query('
    SELECT field_name 
    FROM dynamic_field_definitions 
    ORDER BY id
');
$dynamicFields = $dynamicFieldsStmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($dynamicFields as $fieldName) {
    echo "<th>" . htmlspecialchars($fieldName) . "</th>";
}
?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        s.nom as secteur_name,
        u.name as created_by_name,
        COALESCE(
            (SELECT AVG(note) 
             FROM evaluations e 
             WHERE e.project_id = p.id
            ), 
            0
        ) as note_moyenne,
        GROUP_CONCAT(
            CONCAT(dfd.field_name, ':', COALESCE(pdv.field_value, ''))
            SEPARATOR '||'
        ) as dynamic_fields
    FROM projects p
    LEFT JOIN secteurs s ON p.secteur_id = s.id
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN project_dynamic_values pdv ON p.id = pdv.project_id
    LEFT JOIN dynamic_field_definitions dfd ON pdv.field_id = dfd.id
    GROUP BY p.id, p.nom, p.description, p.status, p.secteur_id, s.nom, u.name
    ORDER BY p.created_at DESC
");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($projects as $project) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($project['id']) . "</td>";
    echo "<td>" . htmlspecialchars($project['nom']) . "</td>";
    echo "<td>" . htmlspecialchars($project['created_by_name'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($project['secteur_name'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($project['created_at']) . "</td>";
    echo "<td>
            <span class='status-badge " . ($project['status'] ?? 'draft') . "'>
                " . htmlspecialchars(getStatusLabel($project['status'])) . "
            </span>
          </td>";
    echo "<td>" . number_format($project['note_moyenne'], 2) . "</td>";
    
    if (!empty($project['dynamic_fields'])) {
        $dynamicFieldsValues = explode('||', $project['dynamic_fields']);
        foreach ($dynamicFieldsValues as $fieldValue) {
            list($fieldName, $value) = explode(':', $fieldValue);
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
    }
    
    echo "<td class='actions'>
            <button class='btn-icon view-btn' onclick='viewProject(" . $project['id'] . ")' title='Voir les détails'>
                <i class='fas fa-eye'></i>
            </button>
            <button class='btn-icon edit-btn' onclick='editProject(" . $project['id'] . ")' title='Modifier'>
                <i class='fas fa-edit'></i>
            </button>
            <button class='btn-icon delete-btn' onclick='deleteProject(" . $project['id'] . ")' title='Supprimer'>
                <i class='fas fa-trash'></i>
            </button>
          </td>";
    echo "</tr>";
}
?>
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
         <!-- Modal pour ajouter/modifier un projet -->
    <div id="project-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un projet</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="project-form" method="POST">
                    <!-- Champs de base du projet -->
                    <div class="form-group">
                        <label for="project-name">Nom du projet</label>
                        <input type="text" id="project-name" name="project_name" required>
                    </div>
                    <div class="form-group">
                        <label for="project-team">Nom de l'équipe</label>
                        <input type="text" id="project-team" name="project_team" required>
                    </div>
                    <div class="form-group">
                        <label for="project-sector">Secteur</label>
                        <select id="project-sector" name="project_sector" required>
                            <?php
                            // Récupérer les secteurs depuis la base de données
                            $secteurStmt = $pdo->query('SELECT id, nom FROM secteurs ORDER BY nom ASC');
                            $secteurs = $secteurStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($secteurs as $secteur) {
                                echo '<option value="' . htmlspecialchars($secteur['id']) . '">'
                                    . htmlspecialchars($secteur['nom']) 
                                    . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="project-description">Description</label>
                        <textarea id="project-description" name="project_description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="project-status">Statut</label>
                        <select id="project-status" name="project_status" required>
                            <option value="draft">Brouillon</option>
                            <option value="submitted">Soumis</option>
                            <option value="under_review">En cours d'évaluation</option>
                            <option value="approved">Approuvé</option>
                            <option value="rejected">Rejeté</option>
                        </select>
                    </div>

                    <!-- Section des champs dynamiques -->
                    <div class="form-group">
                        <!-- <label>Champs supplémentaires</label> -->
                        <div id="dynamic-fields">
                            <?php
                            $dynamicFieldsStmt = $pdo->query('
                                SELECT id, field_name, field_type, is_required 
                                FROM dynamic_field_definitions 
                                ORDER BY id
                            ');
                            $dynamicFields = $dynamicFieldsStmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($dynamicFields as $field) {
                                echo '<div class="dynamic-field">';
                                echo '<label for="dynamic-field-' . htmlspecialchars($field['id']) . '">' 
                                     . htmlspecialchars($field['field_name']) . '</label>';
                                
                                switch ($field['field_type']) {
                                    case 'textarea':
                                        echo '<textarea name="dynamic_fields[' . htmlspecialchars($field['id']) . ']" '
                                             . 'id="dynamic-field-' . htmlspecialchars($field['id']) . '" '
                                             . ($field['is_required'] ? 'required' : '')
                                             . ' rows="4"></textarea>';
                                        break;
                                    
                                    case 'select':
                                        echo '<select name="dynamic_fields[' . htmlspecialchars($field['id']) . ']" '
                                             . 'id="dynamic-field-' . htmlspecialchars($field['id']) . '" '
                                             . ($field['is_required'] ? 'required' : '')
                                             . '>';
                                        echo '<option value="">Sélectionner...</option>';
                                        // Ajouter les options depuis la configuration si nécessaire
                                        echo '</select>';
                                        break;
                                    
                                    default:
                                        echo '<input type="' . htmlspecialchars($field['field_type']) . '" '
                                             . 'name="dynamic_fields[' . htmlspecialchars($field['id']) . ']" '
                                             . 'id="dynamic-field-' . htmlspecialchars($field['id']) . '" '
                                             . ($field['is_required'] ? 'required' : '')
                                             . '>';
                                }
                                echo '</div>';
                            }
                            ?>
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
    <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Validate required fields
        if (empty($_POST['project_name']) || empty($_POST['project_description']) || 
            empty($_POST['project_sector'])) {
            throw new Exception('Tous les champs obligatoires doivent être remplis');
        }

        // Insert or update project
        if (isset($_POST['id'])) {
            $stmt = $pdo->prepare("
                UPDATE projects 
                SET nom = :nom,
                    description = :description,
                    secteur_id = :secteur_id,
                    status = :status
                WHERE id = :id
            ");
            
            $params = [
                'nom' => $_POST['project_name'],
                'description' => $_POST['project_description'],
                'secteur_id' => $_POST['project_sector'],
                'status' => $_POST['project_status'] ?? 'draft',
                'id' => $_POST['id']
            ];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO projects (
                    nom, 
                    description, 
                    secteur_id, 
                    status, 
                    created_by,
                    created_at
                ) VALUES (
                    :nom, 
                    :description, 
                    :secteur_id, 
                    :status, 
                    :created_by,
                    NOW()
                )
            ");
            
            $params = [
                'nom' => $_POST['project_name'],
                'description' => $_POST['project_description'],
                'secteur_id' => $_POST['project_sector'],
                'status' => $_POST['project_status'] ?? 'draft',
                'created_by' => $_SESSION['user_id']
            ];
        }

        $stmt->execute($params);
        $projectId = isset($_POST['id']) ? $_POST['id'] : $pdo->lastInsertId();

        // Handle dynamic fields
        if (!empty($_POST['dynamic_fields'])) {
            if (isset($_POST['id'])) {
                $stmt = $pdo->prepare("DELETE FROM project_dynamic_values WHERE project_id = ?");
                $stmt->execute([$projectId]);
            }

            $stmt = $pdo->prepare("
                INSERT INTO project_dynamic_values (project_id, field_id, field_value)
                VALUES (:project_id, :field_id, :field_value)
            ");

            foreach ($_POST['dynamic_fields'] as $fieldId => $value) {
                $stmt->execute([
                    'project_id' => $projectId,
                    'field_id' => $fieldId,
                    'field_value' => $value
                ]);
            }
        }

        $pdo->commit();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => isset($_POST['id']) ? 'Projet mis à jour avec succès' : 'Projet créé avec succès',
            'projectId' => $projectId
        ]);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erreur save_project: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
        ]);
        exit;
    }
}
?>

    <script src="js/project.js"></script>
</body>
</html>
