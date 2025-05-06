<?php
session_start();
require_once 'auth.php';
requireLogin();
if (!isSuperAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 8px; font-family: Arial, sans-serif; text-align: center;'>
            <h2>Accès refusé</h2>
            <p>Vous devez être super administrateur pour accéder à cette page.</p>
          </div>";
    exit();
}

require_once 'config.php';

$error = '';
$success = '';

// Handle permission updates (simplified example)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $can_vote = isset($_POST['can_vote']) ? 1 : 0;
    $can_admin = isset($_POST['can_admin']) ? 1 : 0;

    if ($user_id > 0) {
        // Update user permissions in the admins table or a dedicated permissions table
        // For this example, assume admins table has can_vote and can_admin columns
        $stmt = $pdo->prepare('UPDATE admins SET can_vote = ?, can_admin = ? WHERE id = ?');
        if ($stmt->execute([$can_vote, $can_admin, $user_id])) {
            $success = "Permissions mises à jour avec succès.";
        } else {
            $error = "Erreur lors de la mise à jour des permissions.";
        }
    } else {
        $error = "Utilisateur invalide.";
    }
}

// Fetch all admins for management
$stmt = $pdo->query('SELECT id, name, email, can_vote, can_admin FROM admins ORDER BY id DESC');
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Droits Utilisateurs - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #00843F;
            margin-bottom: 20px;
            text-align: center;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #00843F;
            color: white;
        }
        input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
        }
        button.btn-primary {
            background-color: #00843F;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        button.btn-primary:hover {
            background-color: #006b32;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion des Droits Utilisateurs</h2>
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="user_permissions.php">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Peut voter</th>
                        <th>Peut administrer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['id']) ?></td>
                        <td><?= htmlspecialchars($admin['name']) ?></td>
                        <td><?= htmlspecialchars($admin['email']) ?></td>
                        <td><input type="checkbox" name="can_vote" value="1" <?= $admin['can_vote'] ? 'checked' : '' ?>></td>
                        <td><input type="checkbox" name="can_admin" value="1" <?= $admin['can_admin'] ? 'checked' : '' ?>></td>
                        <td>
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($admin['id']) ?>">
                            <button type="submit" class="btn-primary">Mettre à jour</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($admins)): ?>
                    <tr><td colspan="6" style="text-align:center;">Aucun administrateur trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
</body>
</html>
