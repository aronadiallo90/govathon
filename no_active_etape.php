<?php
session_start();
require_once 'includes/db.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aucune Étape Active - GOVATHON</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .no-etape-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            text-align: center;
            padding: 2rem;
        }

        .no-etape-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .no-etape-title {
            font-size: 2rem;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .no-etape-message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <div class="no-etape-container">
                <i class="fas fa-clock no-etape-icon"></i>
                <h1 class="no-etape-title">Aucune Étape Active</h1>
                <p class="no-etape-message">
                    Il n'y a actuellement aucune étape active pour le vote. 
                    Veuillez attendre qu'une étape soit activée par les administrateurs 
                    ou contactez-les si vous pensez qu'il s'agit d'une erreur.
                </p>
                <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                    <a href="etapes.php" class="btn-primary">
                        <i class="fas fa-cog"></i>
                        Gérer les étapes
                    </a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 