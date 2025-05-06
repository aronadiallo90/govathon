<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    try {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            throw new Exception('Email et mot de passe requis');
        }
        
        $user = getUserByEmail($pdo, $email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Vérifier le rôle dans la nouvelle structure
            if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_logged_in'] = true;
                
                $response = [
                    'success' => true,
                    'redirect' => 'index.php',
                    'debug' => [
                        'message' => 'Login successful',
                        'role' => $user['role']
                    ]
                ];
            } else {
                throw new Exception('Accès non autorisé');
            }
        } else {
            throw new Exception('Email ou mot de passe incorrect');
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Connexion - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
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
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }
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
    <div class="login-container">
        <h2>Connexion</h2>
        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required autofocus />
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required />
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
        <div class="login-links" style="margin-top: 15px; text-align: center;">
            <a href="forgot_password.php" style="color: #00843F; text-decoration: none; margin-right: 15px;">Mot de passe oublié ?</a>
            <a href="register.php" style="color: #00843F; text-decoration: none;">Créer un compte</a>
        </div>
    </div>
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('login.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Response:', data); // Debug log
            if (data.success) {
                window.location.replace(data.redirect);
            } else {
                const errorDiv = document.querySelector('.error-message') || document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = data.message || 'Une erreur est survenue';
                if (!document.querySelector('.error-message')) {
                    document.querySelector('form').prepend(errorDiv);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la connexion');
        });
    });
    </script>
</body>
</html>
