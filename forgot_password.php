<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if ($email) {
        // Vérifier si l'email existe
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        
        if ($user = $stmt->fetch()) {
            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Sauvegarder le token
            $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?');
            $stmt->execute([$token, $expires, $email]);
            
            // Envoyer l'email
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/cursor/reset_password.php?token=" . $token;
            $to = $email;
            $subject = "Réinitialisation de votre mot de passe - GOVATHON";
            $message = "Bonjour,\n\n";
            $message .= "Vous avez demandé la réinitialisation de votre mot de passe.\n\n";
            $message .= "Cliquez sur le lien suivant pour réinitialiser votre mot de passe :\n";
            $message .= $resetLink . "\n\n";
            $message .= "Ce lien expirera dans 1 heure.\n\n";
            $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
            $message .= "Cordialement,\nL'équipe GOVATHON";
            
            $headers = "From: noreply@govathon.com\r\n";
            $headers .= "Reply-To: noreply@govathon.com\r\n";
            
            if (mail($to, $subject, $message, $headers)) {
                $success = 'Un email de réinitialisation a été envoyé. Vérifiez votre boîte de réception.';
            } else {
                $error = 'Erreur lors de l\'envoi de l\'email.';
            }
        } else {
            // Pour des raisons de sécurité, ne pas indiquer si l'email existe ou non
            $success = 'Si votre email existe dans notre base, vous recevrez un lien de réinitialisation.';
        }
    } else {
        $error = 'Veuillez entrer votre email.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .forgot-password-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #00843F;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn-primary {
            width: 100%;
            background-color: #00843F;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #006b32;
        }
        .error-message, .success-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .success-message {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Mot de passe oublié</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <button type="submit" class="btn-primary">Réinitialiser le mot de passe</button>
        </form>
        <div style="margin-top: 15px; text-align: center;">
            <a href="login.php" style="color: #00843F; text-decoration: none;">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>