<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification que l'utilisateur est connecté et est un participant
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'participant') {
    header('Location: login.php');
    exit;
}

// Récupération des secteurs
$stmt = $pdo->query("SELECT id, nom FROM secteurs ORDER BY nom ASC");
$secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $secteur_id = $_POST['secteur_id'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Insertion du projet
        $stmt = $pdo->prepare("
            INSERT INTO projects (nom, description, secteur_id, created_by, status)
            VALUES (?, ?, ?, ?, 'submitted')
        ");
        $stmt->execute([$nom, $description, $secteur_id, $user_id]);
        $project_id = $pdo->lastInsertId();
        
        // Récupération de l'étape de présélection
        $stmt = $pdo->prepare("SELECT id FROM etapes WHERE nom = 'Présélection' LIMIT 1");
        $stmt->execute();
        $etape = $stmt->fetch();
        
        if ($etape) {
            // Association du projet à l'étape de présélection
            $stmt = $pdo->prepare("
                INSERT INTO project_etapes (project_id, etape_id, status)
                VALUES (?, ?, 'en_cours')
            ");
            $stmt->execute([$project_id, $etape['id']]);
        }
        
        $pdo->commit();
        $success = "Votre projet a été soumis avec succès et est en attente de présélection.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de la soumission du projet.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soumettre un Projet - GOVATHON</title>
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
                    <h2>Soumettre un Projet</h2>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="project-form">
                    <div class="form-group">
                        <label for="nom">Nom du projet</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="6" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="secteur_id">Secteur</label>
                        <select id="secteur_id" name="secteur_id" required>
                            <option value="">Sélectionnez un secteur</option>
                            <?php foreach ($secteurs as $secteur): ?>
                                <option value="<?php echo $secteur['id']; ?>">
                                    <?php echo htmlspecialchars($secteur['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Soumettre le projet</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 