<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

// Récupération des critères
$stmt = $pdo->query("SELECT * FROM criteres ORDER BY id ASC");
$criteres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Critères - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styles généraux */
        .criteres-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .criteres-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Style du bouton d'ajout */
        #add-critere-btn {
            background-color: #00843F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        #add-critere-btn:hover {
            background-color: #006632;
        }

        #add-critere-btn i {
            font-size: 1.1em;
        }

        .criteres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .critere-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .critere-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .critere-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .critere-info h3 {
            margin: 0;
            color: #333;
            font-size: 1.2em;
        }

        .critere-coefficient {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .critere-actions {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s;
        }

        .btn-icon:hover {
            color: #333;
        }

        .btn-icon.edit-critere:hover {
            color: #1976d2;
        }

        .btn-icon.delete-critere:hover {
            color: #d32f2f;
        }

        .critere-details {
            color: #666;
        }

        .critere-details p {
            margin: 10px 0;
            line-height: 1.4;
        }

        /* Styles du modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-20px);
            transition: transform 0.3s;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #666;
        }

        /* Styles du formulaire */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background-color: #00843F;
            color: white;
        }

        .btn-primary:hover {
            background-color: #006632;
        }

        .btn-primary:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        /* Styles des notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1001;
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background-color: #2e7d32;
        }

        .notification.error {
            background-color: #d32f2f;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Styles responsifs */
        @media (max-width: 768px) {
            .criteres-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <?php include 'components/header.php'; ?>

            <div class="data-management-content">
                <div class="data-header">
                    <h2>Gestion des Critères</h2>
                    <button id="add-critere-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un critère
                    </button>
                </div>

                <div class="data-table-container">
                    <div class="criteres-grid">
                        <?php foreach ($criteres as $critere): ?>
                            <div class="critere-card" data-critere-id="<?php echo $critere['id']; ?>">
                                <div class="critere-header">
                                    <div class="critere-info">
                                        <h3><?php echo htmlspecialchars($critere['nom']); ?></h3>
                                        <span class="critere-coefficient">
                                            Coefficient: <?php echo $critere['coefficient']; ?>
                                        </span>
                                    </div>
                                    <div class="critere-actions">
                                        <button class="btn-icon edit-critere" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon delete-critere" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="critere-details">
                                    <p><?php echo htmlspecialchars($critere['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier un critère -->
    <div id="critere-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter un critère</h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="critere-form">
                    <input type="hidden" id="critereId" name="critereId">
                    
                    <div class="form-group">
                        <label for="nom">Nom du critère</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="coefficient">Coefficient</label>
                        <input type="number" id="coefficient" name="coefficient" min="1" max="5" step="0.5" required>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="cancel-btn">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Éléments DOM
        const modal = document.getElementById('critere-modal');
        const addCritereBtn = document.getElementById('add-critere-btn');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancel-btn');
        const critereForm = document.getElementById('critere-form');
        const criteresGrid = document.querySelector('.criteres-grid');

        // État de chargement
        let isLoading = false;

        // Fonction pour rafraîchir la liste des critères
        async function refreshCriteres() {
            try {
                const response = await fetch('criteres.php');
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.querySelector('.criteres-grid');
                if (newGrid) {
                    criteresGrid.innerHTML = newGrid.innerHTML;
                    attachCritereActions();
                }
            } catch (error) {
                console.error('Erreur lors du rafraîchissement:', error);
                showNotification('Erreur lors du rafraîchissement de la liste', 'error');
            }
        }

        // Fonction pour afficher/masquer le chargement
        function setLoading(loading) {
            console.log('Changement de l\'état de chargement:', loading);
            isLoading = loading;
            const submitBtn = critereForm.querySelector('button[type="submit"]');
            if (loading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Enregistrer';
            }
        }

        // Fonction pour afficher une notification
        function showNotification(message, type = 'success') {
            console.log('Notification:', message, type);
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // Fonction pour valider les données
        function validateCritereData(data) {
            console.log('Validation des données:', data);
            const errors = [];
            
            // Validation du nom
            if (!data.nom.trim()) {
                errors.push('Le nom est requis');
            } else if (data.nom.trim().length < 3) {
                errors.push('Le nom doit contenir au moins 3 caractères');
            }

            // Validation de la description
            if (!data.description.trim()) {
                errors.push('La description est requise');
            } else if (data.description.trim().length < 10) {
                errors.push('La description doit contenir au moins 10 caractères');
            }

            // Validation du coefficient
            if (!data.coefficient || data.coefficient < 1 || data.coefficient > 5) {
                errors.push('Le coefficient doit être entre 1 et 5');
            }

            return errors;
        }

        // Fonction pour afficher le modal
        function showModal(critere = null) {
            console.log('Affichage du modal avec les données:', critere);
            
            const modal = document.getElementById('critere-modal');
            if (!modal) {
                console.error('Modal non trouvé dans le DOM');
                return;
            }

            if (critere) {
                console.log('Remplissage du formulaire avec les données:', critere);
                document.getElementById('critereId').value = critere.id;
                document.getElementById('nom').value = critere.nom;
                document.getElementById('description').value = critere.description;
                document.getElementById('coefficient').value = critere.coefficient;
                document.querySelector('.modal-header h3').textContent = 'Modifier un critère';
            } else {
                console.log('Réinitialisation du formulaire pour nouveau critère');
                critereForm.reset();
                document.getElementById('critereId').value = '';
                document.querySelector('.modal-header h3').textContent = 'Ajouter un critère';
            }

            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.classList.add('show');
            });
        }

        // Fonction pour masquer le modal
        function hideModal() {
            console.log('Masquage du modal');
            const modal = document.getElementById('critere-modal');
            if (!modal) return;

            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                critereForm.reset();
            }, 300);
        }

        // Gestionnaires d'événements
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM chargé, initialisation des événements');
            
            // Bouton d'ajout
            if (addCritereBtn) {
                addCritereBtn.addEventListener('click', () => {
                    console.log('Clic sur le bouton d\'ajout');
                    showModal();
                });
            }

            // Boutons de fermeture du modal
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', hideModal);
            }
            if (cancelBtn) {
                cancelBtn.addEventListener('click', hideModal);
            }

            // Formulaire
            if (critereForm) {
                critereForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    console.log('Soumission du formulaire');
                    if (isLoading) return;

                    const formData = new FormData(critereForm);
                    const critereData = {
                        critereId: formData.get('critereId'),
                        nom: formData.get('nom'),
                        description: formData.get('description'),
                        coefficient: formData.get('coefficient')
                    };

                    console.log('Données du formulaire:', critereData);

                    // Validation
                    const errors = validateCritereData(critereData);
                    if (errors.length > 0) {
                        showNotification(errors.join('<br>'), 'error');
                        return;
                    }

                    try {
                        setLoading(true);
                        const response = await fetch('actions/save_critere.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(critereData)
                        });

                        const data = await response.json();
                        console.log('Réponse du serveur:', data);

                        if (data.success) {
                            showNotification(data.message);
                            hideModal();
                            await refreshCriteres();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        showNotification('Une erreur est survenue lors de l\'enregistrement du critère', 'error');
                    } finally {
                        setLoading(false);
                    }
                });
            }

            // Attacher les événements initialement
            attachCritereActions();
        });

        // Fonction pour attacher les événements aux cartes
        function attachCritereActions() {
            console.log('Attachement des événements aux cartes');
            
            // Édition
            document.querySelectorAll('.edit-critere').forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Clic sur le bouton d\'édition');
                    
                    const critereCard = this.closest('.critere-card');
                    if (!critereCard) {
                        console.error('Carte de critère non trouvée');
                        return;
                    }
                    
                    const critereId = critereCard.dataset.critereId;
                    console.log('ID du critère à modifier:', critereId);

                    try {
                        setLoading(true);
                        const response = await fetch(`actions/get_critere.php?id=${critereId}`);
                        const data = await response.json();
                        console.log('Données du critère reçues:', data);
                        
                        if (data.success && data.critere) {
                            console.log('Affichage du modal avec les données:', data.critere);
                            showModal(data.critere);
                        } else {
                            console.error('Erreur dans la réponse:', data);
                            showNotification(data.message || 'Erreur lors de la récupération des données', 'error');
                        }
                    } catch (error) {
                        console.error('Erreur lors de la récupération des données:', error);
                        showNotification('Une erreur est survenue lors de la récupération des données', 'error');
                    } finally {
                        setLoading(false);
                    }
                });
            });

            // Suppression
            document.querySelectorAll('.delete-critere').forEach(button => {
                button.addEventListener('click', async function() {
                    console.log('Clic sur le bouton de suppression');
                    if (isLoading) return;
                    const critereCard = this.closest('.critere-card');
                    const critereId = critereCard.dataset.critereId;

                    if (confirm('Êtes-vous sûr de vouloir supprimer ce critère ?')) {
                        try {
                            setLoading(true);
                            const response = await fetch('actions/delete_critere.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ id: critereId })
                            });

                            const data = await response.json();
                            console.log('Réponse de suppression:', data);
                            if (data.success) {
                                showNotification(data.message);
                                await refreshCriteres();
                            } else {
                                showNotification(data.message, 'error');
                            }
                        } catch (error) {
                            console.error('Erreur:', error);
                            showNotification('Une erreur est survenue lors de la suppression', 'error');
                        } finally {
                            setLoading(false);
                        }
                    }
                });
            });
        }
    </script>
</body>
</html> 