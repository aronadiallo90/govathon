<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $verification_code = $_POST['verification_code'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // Vérification du code
        $stmt = $pdo->prepare("
            SELECT p.id, p.nom, p.description, p.secteur_id 
            FROM projects p
            JOIN project_dynamic_values pdv_email ON p.id = pdv_email.project_id
            JOIN dynamic_field_definitions dfd_email ON pdv_email.field_id = dfd_email.id
            JOIN project_dynamic_values pdv_code ON p.id = pdv_code.project_id
            JOIN dynamic_field_definitions dfd_code ON pdv_code.field_id = dfd_code.id
            WHERE dfd_email.field_name = 'Email'
            AND pdv_email.field_value = ?
            AND dfd_code.field_name = 'Verification Code'
            AND pdv_code.field_value = ?
            AND p.status = 'pending'
            AND p.created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$email, $verification_code]);
        $project = $stmt->fetch();
        
        if ($project) {
            // Mise à jour du statut du projet
            $stmt = $pdo->prepare("
                UPDATE projects 
                SET status = 'submitted'
                WHERE id = ?
            ");
            $stmt->execute([$project['id']]);
            
            // Ajout du projet à l'étape en cours
            $stmt = $pdo->prepare("
                INSERT INTO project_etapes (project_id, etape_id, status)
                SELECT ?, id, 'en_cours'
                FROM etapes 
                WHERE statut = 'en_cours'
                LIMIT 1
            ");
            $stmt->execute([$project['id']]);
            
            $pdo->commit();
            $success = "Votre projet a été vérifié et soumis avec succès !";
        } else {
            $error = "Code de vérification invalide ou expiré.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de la vérification du projet.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du Projet - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/projects.css">
</head>
<body>
    <div class="container">
        <main class="main-content">
            <div class="verification-content">
                <h1>Vérification du Projet</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="verification-success">
                        <p>Merci d'avoir soumis votre projet !</p>
                        <a href="public_project_submit.php" class="btn-primary">Retour à l'accueil</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" class="verification-form">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        
                        <div class="form-group">
                            <label for="verification_code">Code de vérification</label>
                            <input type="text" id="verification_code" name="verification_code" required>
                            <small>Le code est valable pendant 15 minutes</small>
                        </div>
                        
                        <button type="submit" class="btn-primary">Vérifier</button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 