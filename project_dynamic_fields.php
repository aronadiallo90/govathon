<?php
session_start();
require_once 'includes/db.php';

// Vérifier les droits d'accès superadmin avec la nouvelle structure
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: unauthorized.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $field_name = trim($_POST['field_name'] ?? '');
        $field_type = $_POST['field_type'] ?? 'text';
        $is_required = isset($_POST['is_required']) ? 1 : 0;

        if (!empty($field_name)) {
            $stmt = $pdo->prepare("
                INSERT INTO dynamic_field_definitions 
                (field_name, field_type, is_required) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$field_name, $field_type, $is_required]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Gestion des Champs Dynamiques - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>
        
        <main class="main-content">
            <header>
                <h2>Gestion des Champs Dynamiques</h2>
            </header>

            <!-- Formulaire d'ajout -->
            <div class="form-container">
                <form method="POST" class="dynamic-field-form">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label for="field_name">Nom du champ</label>
                        <input type="text" id="field_name" name="field_name" required 
                               placeholder="Ex: Téléphone, Email, etc.">
                    </div>

                    <div class="form-group">
                        <label for="field_type">Type de champ</label>
                        <select id="field_type" name="field_type">
                            <option value="text">Texte</option>
                            <option value="number">Nombre</option>
                            <option value="date">Date</option>
                            <option value="email">Email</option>
                            <option value="tel">Téléphone</option>
                        </select>
                    </div>

                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="is_required">
                            Champ obligatoire
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus"></i> Ajouter le champ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des champs dynamiques -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom du champ</th>
                            <th>Type</th>
                            <th>Obligatoire</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $stmt = $pdo->query('
                        SELECT * FROM dynamic_field_definitions 
                        ORDER BY created_at DESC
                    ');
                    while ($field = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($field['field_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($field['field_type']) . "</td>";
                        echo "<td>" . ($field['is_required'] ? 'Oui' : 'Non') . "</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($field['created_at'])) . "</td>";
                        echo "<td class='actions'>";
                        echo "<button class='btn-danger' onclick='deleteField({$field['id']})'>";
                        echo "<i class='fas fa-trash'></i></button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    function deleteField(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce champ ?')) {
            fetch('delete_dynamic_field.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression');
                }
            });
        }
    }
    </script>
</body>
</html>