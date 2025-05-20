<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/Mailer.php';

// Récupération des secteurs
$stmt = $pdo->query("SELECT * FROM secteurs ORDER BY nom ASC");
$secteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
$verification_sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $email = $_POST['email'] ?? '';
    $secteur_id = $_POST['secteur_id'] ?? '';
    
    // Génération du code de vérification
    $verification_code = bin2hex(random_bytes(3));
    $verification_code = strtoupper($verification_code);
    
    try {
        $pdo->beginTransaction();
        
        // Vérification si l'email existe déjà dans un projet
        $stmt = $pdo->prepare("
            SELECT p.id 
            FROM projects p 
            JOIN project_dynamic_values pdv ON p.id = pdv.project_id 
            JOIN dynamic_field_definitions dfd ON pdv.field_id = dfd.id 
            WHERE dfd.field_name = 'Email' 
            AND pdv.field_value = ?
        ");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cette adresse email est déjà utilisée pour un projet.";
        } else {
            // Création du projet avec statut 'pending'
            $stmt = $pdo->prepare("
                INSERT INTO projects (nom, description, secteur_id, status, created_by)
                VALUES (?, ?, ?, 'pending', 0)
            ");
            $stmt->execute([$nom, $description, $secteur_id]);
            $project_id = $pdo->lastInsertId();
            
            // Enregistrement de l'email comme champ dynamique
            $stmt = $pdo->prepare("
                INSERT INTO project_dynamic_values (project_id, field_id, field_value)
                SELECT ?, id, ?
                FROM dynamic_field_definitions
                WHERE field_name = 'Email'
            ");
            $stmt->execute([$project_id, $email]);
            
            // Enregistrement du code de vérification
            $stmt = $pdo->prepare("
                INSERT INTO project_dynamic_values (project_id, field_id, field_value)
                SELECT ?, id, ?
                FROM dynamic_field_definitions
                WHERE field_name = 'Verification Code'
            ");
            $stmt->execute([$project_id, $verification_code]);
            
            // Envoi du code de vérification par email
            $mailer = new Mailer();
            if ($mailer->sendVerificationCode($email, $verification_code)) {
                $pdo->commit();
                $verification_sent = true;
                $success = "Un code de vérification a été envoyé à votre adresse email.";
            } else {
                throw new Exception("Erreur lors de l'envoi de l'email");
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de l'enregistrement du projet.";
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
                
                <?php if ($verification_sent): ?>
                    <div class="verification-form">
                        <h2>Vérification du projet</h2>
                        <form method="POST" action="verify_project.php">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <div class="form-group">
                                <label for="verification_code">Code de vérification</label>
                                <input type="text" id="verification_code" name="verification_code" required>
                                <small>Le code a été envoyé à votre adresse email</small>
                            </div>
                            <button type="submit" class="btn-primary">Vérifier</button>
                        </form>
                    </div>
                <?php else: ?>
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
                            <small>Un code de vérification sera envoyé à cette adresse</small>
                        </div>
                        
                        <button type="submit" class="btn-primary">Soumettre le projet</button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 