<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';
$validToken = false;
$token = $_GET['token'] ?? '';

if ($token) {
    // Vérifier si le token est valide et non expiré
    $stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()');
    $stmt->execute([$token]);
    $validToken = $stmt->fetch() !== false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password && $confirm_password) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe et effacer le token
            $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?');
            if ($stmt->execute([$hashed_password, $token])) {
                $success = 'Votre mot de passe a été réinitialisé avec succès.';
                header("Refresh: 2; url=login.php");
            } else {
                $error = 'Erreur lors de la réinitialisation du mot de passe.';
            }
        } else {
            $error = 'Les mots de passe ne correspondent pas.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Same CSS as forgot_password.php -->
</head>
<body>
    <div class="forgot-password-container">
        <h2>Réinitialiser le mot de passe</h2>
        <?php if (!$validToken): ?>
            <div class="error-message">Le lien de réinitialisation est invalide ou a expiré.</div>
            <div style="text-align: center; margin-top: 15px;">
                <a href="forgot_password.php" style="color: #00843F; text-decoration: none;">Demander un nouveau lien</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe :</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-primary">Changer le mot de passe</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>