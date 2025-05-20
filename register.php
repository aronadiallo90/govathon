<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Vérification de l'unicité de l'email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Cet email est déjà utilisé";
    } else {
        // Vérification de l'unicité du téléphone
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = "Ce numéro de téléphone est déjà utilisé";
        } else {
            // Création du token de vérification
            $verification_token = bin2hex(random_bytes(32));
            
            // Insertion de l'utilisateur
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password, type, verification_token)
                VALUES (?, ?, ?, ?, 'participant', ?)
            ");
            
            if ($stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $verification_token])) {
                // Envoi de l'email de vérification
                $verification_link = "http://votre-domaine.com/verify.php?token=" . $verification_token;
                $to = $email;
                $subject = "Vérification de votre compte GOVATHON";
                $message = "Bonjour $name,\n\n";
                $message .= "Merci de vous être inscrit sur GOVATHON. Pour activer votre compte, ";
                $message .= "veuillez cliquer sur le lien suivant :\n\n";
                $message .= $verification_link;
                
                mail($to, $subject, $message);
                
                $success = "Inscription réussie ! Un email de vérification vous a été envoyé.";
            } else {
                $error = "Une erreur est survenue lors de l'inscription";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/auth.css">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
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
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c2c7;
        }
        .success-message {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #badbcc;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button.btn-primary {
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
        button.btn-primary:hover {
            background-color: #006b32;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Inscription</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Numéro de téléphone</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary">S'inscrire</button>
            </form>
            
            <p class="auth-links">
                Déjà inscrit ? <a href="login.php">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
