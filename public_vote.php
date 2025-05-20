<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Récupération des projets en cours de vote
$stmt = $pdo->prepare("
    SELECT p.*, s.nom as secteur_nom, 
           COUNT(DISTINCT v.id) as total_votes
    FROM projects p
    JOIN secteurs s ON p.secteur_id = s.id
    JOIN project_etapes pe ON p.id = pe.project_id
    JOIN etapes e ON pe.etape_id = e.id
    LEFT JOIN public_votes v ON p.id = v.project_id
    WHERE e.statut = 'en_cours'
    AND pe.status = 'valide'
    GROUP BY p.id
    ORDER BY total_votes DESC
");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
$verification_sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $verification_method = $_POST['verification_method'] ?? '';
    
    // Génération d'un code de vérification plus sécurisé
    $verification_code = bin2hex(random_bytes(3)); // Génère 6 caractères hexadécimaux
    $verification_code = strtoupper($verification_code); // Convertit en majuscules
    
    try {
        $pdo->beginTransaction();
        
        // Vérification si le contact a déjà voté
        $stmt = $pdo->prepare("SELECT id FROM public_votes WHERE contact = ? AND is_verified = 1");
        $stmt->execute([$contact]);
        if ($stmt->fetch()) {
            $error = "Vous avez déjà voté pour un projet.";
        } else {
            // Enregistrement du vote en attente de vérification
            $stmt = $pdo->prepare("
                INSERT INTO public_votes (project_id, contact, verification_code, verification_method)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$project_id, $contact, $verification_code, $verification_method]);
            
            // Envoi du code de vérification selon la méthode choisie
            switch ($verification_method) {
                case 'email':
                    $subject = "Votre code de vérification GOVATHON";
                    $message = "Votre code de vérification est : $verification_code\n\n";
                    $message .= "Ce code est valable pendant 15 minutes.";
                    mail($contact, $subject, $message);
                    break;
                    
                case 'sms':
                    // Intégration avec un service SMS (ex: Twilio)
                    // TODO: Implémenter l'envoi SMS
                    break;
                    
                case 'whatsapp':
                    // Intégration avec l'API WhatsApp
                    // TODO: Implémenter l'envoi WhatsApp
                    break;
            }
            
            $pdo->commit();
            $verification_sent = true;
            $success = "Un code de vérification a été envoyé à votre $verification_method.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur est survenue lors de l'enregistrement du vote.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Public - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/votes.css">
</head>
<body>
    <div class="container">
        <main class="main-content">
            <div class="vote-content">
                <h1>Vote Public - GOVATHON</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($verification_sent): ?>
                    <div class="verification-form">
                        <h2>Vérification du vote</h2>
                        <form method="POST" action="verify_vote.php">
                            <input type="hidden" name="contact" value="<?php echo htmlspecialchars($contact); ?>">
                            <div class="form-group">
                                <label for="verification_code">Code de vérification</label>
                                <input type="text" id="verification_code" name="verification_code" required>
                            </div>
                            <button type="submit" class="btn-primary">Vérifier</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="projects-grid">
                        <?php foreach ($projects as $project): ?>
                            <div class="project-card">
                                <div class="project-header">
                                    <h3><?php echo htmlspecialchars($project['nom']); ?></h3>
                                    <span class="votes-count">
                                        <?php echo $project['total_votes']; ?> votes
                                    </span>
                                </div>
                                
                                <div class="project-info">
                                    <p><strong>Secteur:</strong> <?php echo htmlspecialchars($project['secteur_nom']); ?></p>
                                </div>
                                
                                <div class="project-description">
                                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                                </div>
                                
                                <form method="POST" action="" class="vote-form">
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label for="contact">Votre contact</label>
                                        <input type="text" id="contact" name="contact" required
                                               placeholder="Email ou numéro de téléphone">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="verification_method">Méthode de vérification</label>
                                        <select id="verification_method" name="verification_method" required>
                                            <option value="email">Email</option>
                                            <option value="sms">SMS</option>
                                            <option value="whatsapp">WhatsApp</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn-primary">Voter</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 