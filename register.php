<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name && $email && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                // Insérer l'utilisateur
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $success = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
                    // Ajouter la redirection après 2 secondes
                    header("Refresh: 2; url=login.php");
                } else {
                    $error = 'Erreur lors de l\'inscription.';
                }
            }
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Créer un compte - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
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
    <div class="register-container">
        <h2>Créer un compte</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" action="register.php">
            <div class="form-group">
                <label for="name">Nom complet :</label>
                <input type="text" id="name" name="name" required autofocus />
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required />
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required />
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required />
            </div>
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>
        <div style="margin-top: 15px; text-align: center;">
            <a href="login.php" style="color: #00843F; text-decoration: none;">Déjà un compte ? Connectez-vous</a>
        </div>
    </div>
</body>
</html>
