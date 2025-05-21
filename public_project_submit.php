<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
// require_once 'includes/Mailer.php'; // Plus nécessaire

// Récupération des secteurs
$stmt = $pdo->query("SELECT * FROM secteurs ORDER BY nom ASC");
$secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
// $verification_sent = false; // Plus nécessaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $email = $_POST['email'] ?? '';
    $secteur_id = $_POST['secteur_id'] ?? '';
    
    // Validation côté serveur
    if (empty($nom) || empty($description) || empty($email) || empty($secteur_id)) {
        $error = "Veuillez remplir tous les champs requis.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else if (!is_numeric($secteur_id) || $secteur_id <= 0) {
         $error = "Veuillez sélectionner un secteur valide.";
    }
    
    try {
        $pdo->beginTransaction();
        
        // Vérification si l'email existe déjà dans un projet validé ou en attente
        $stmt = $pdo->prepare("
            SELECT p.id 
            FROM projects p 
            JOIN project_dynamic_values pdv ON p.id = pdv.project_id 
            JOIN dynamic_field_definitions dfd ON pdv.field_id = dfd.id 
            WHERE dfd.field_name = 'Email' 
            AND pdv.field_value = ?
            AND p.status IN ('pending', 'submitted', 'under_review', 'approved') -- Vérifie aussi dans les statuts non rejetés
        ");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cette adresse email est déjà utilisée pour un projet soumis.";
        } else {
            // Création du projet avec statut 'pending' pour présélection
            $stmt = $pdo->prepare("INSERT INTO projects (nom, description, secteur_id, status, created_by) VALUES (?, ?, ?, 'pending', NULL)");
            $stmt->execute([$nom, $description, $secteur_id]);
            $project_id = $pdo->lastInsertId();

            // Ajouter directement à l'étape Inscription
            $stmt = $pdo->prepare("
                INSERT INTO project_etapes (project_id, etape_id, status)
                SELECT ?, id, 'en_cours'
                FROM etapes 
                WHERE nom = 'Inscription'
            ");
            $stmt->execute([$project_id]);

            // Sauvegarder l'email
            $stmt = $pdo->prepare("
                INSERT INTO project_dynamic_values (project_id, field_id, field_value)
                SELECT ?, id, ?
                FROM dynamic_field_definitions
                WHERE field_name = 'Email'
            ");
            $stmt->execute([$project_id, $email]);

            $pdo->commit();
            $success = "Votre projet a été soumis avec succès et est en étape d'inscription.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de l'enregistrement du projet : " . $e->getMessage(); // Afficher l'erreur pour le debug
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
        <main class="main-content">
            <div class="project-submit-content">
                <h1>Soumettre un Projet - GOVATHON</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php // if ($verification_sent): ?>
                    <?php /* Plus nécessaire */ ?>
                <?php // else: ?>
                    <form method="POST" action="" class="project-form">
                        <div class="form-group">
                            <label for="nom">Nom du projet <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description du projet <span class="required">*</span></label>
                            <textarea id="description" name="description" rows="6" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="secteur_id">Secteur <span class="required">*</span></label>
                            <select id="secteur_id" name="secteur_id" required>
                                <option value="">Sélectionnez un secteur</option>
                                <?php foreach ($secteurs as $secteur): ?>
                                    <option value="<?php echo $secteur['id']; ?>">
                                        <?php echo htmlspecialchars($secteur['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                            <small>Cet email doit être unique pour chaque projet soumis.</small>
                        </div>
                        
                        <button type="submit" class="btn-primary">Soumettre le projet</button>
                    </form>
                <?php // endif; ?>
            </div>
        </main>
    </div>
</body>
</html>