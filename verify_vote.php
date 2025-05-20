<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = $_POST['contact'] ?? '';
    $verification_code = $_POST['verification_code'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // Vérification du code
        $stmt = $pdo->prepare("
            SELECT id, project_id, created_at 
            FROM public_votes 
            WHERE contact = ? 
            AND verification_code = ? 
            AND is_verified = 0
            AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$contact, $verification_code]);
        $vote = $stmt->fetch();
        
        if ($vote) {
            // Mise à jour du vote comme vérifié
            $stmt = $pdo->prepare("
                UPDATE public_votes 
                SET is_verified = 1, 
                    verified_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$vote['id']]);
            
            $pdo->commit();
            $success = "Votre vote a été enregistré avec succès !";
        } else {
            $error = "Code de vérification invalide ou expiré.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de la vérification du vote.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du Vote - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/votes.css">
</head>
<body>
    <div class="container">
        <main class="main-content">
            <div class="verification-content">
                <h1>Vérification du Vote</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="verification-success">
                        <p>Merci d'avoir participé au vote !</p>
                        <a href="public_vote.php" class="btn-primary">Retour aux projets</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" class="verification-form">
                        <input type="hidden" name="contact" value="<?php echo htmlspecialchars($contact); ?>">
                        
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