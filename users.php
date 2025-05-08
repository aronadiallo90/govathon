<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'Admin';

// Fonction pour obtenir les initiales
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= mb_substr($word, 0, 1);
    }
    return mb_strtoupper($initials);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Utilisateurs - GOVATHON</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="css/users.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .user-profile .jury-avatar {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            color: white;
            font-weight: bold;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                    <h2>Gestion des Utilisateurs</h2>
                    <button class="btn-primary" id="add-user-btn">
                        <i class="fas fa-plus"></i> Ajouter un utilisateur
                    </button>
                </div>
            </header>

            <div class="data-table-container">
                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Secteur</th>
                            <th>Actif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- Users will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for Add/Edit User -->
    <div class="modal" id="user-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Ajouter un utilisateur</h3>
                <button class="close-modal" id="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <input type="hidden" id="user-id" name="id" />
                    <div class="form-group">
                        <label for="user-name">Nom</label>
                        <input type="text" id="user-name" name="name" required />
                    </div>
                    <div class="form-group">
                        <label for="user-email">Email</label>
                        <input type="email" id="user-email" name="email" required />
                    </div>
                    <!-- Password field removed for update user as per request -->
                    <div class="form-group">
                        <label for="user-role">Rôle</label>
                        <select id="user-role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="jury">Jury</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user-secteur">Secteur</label>
                        <select id="user-secteur" name="secteur_id">
                            <option value="">Aucun</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user-active">Actif</label>
                        <select id="user-active" name="is_active" required>
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user-global-jury">Jury Global</label>
                        <select id="user-global-jury" name="is_global_jury" required>
                            <option value="0">Non</option>
                            <option value="1">Oui</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/users.js"></script>
</body>
</html>
