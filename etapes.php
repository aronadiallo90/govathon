<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

// Récupération des étapes
$stmt = $pdo->query("SELECT * FROM etapes ORDER BY ordre ASC");
$etapes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étapes - GOVATHON</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/data-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styles généraux */
        .etapes-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .etapes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .etapes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        /* Styles des cartes */
        .etape-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .etape-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .etape-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .etape-info h3 {
            margin: 0;
            color: #333;
            font-size: 1.2em;
        }

        .etape-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .etape-status.en_cours {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .etape-status.terminee {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .etape-status.a_venir {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .etape-actions {
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

        .btn-icon.edit-etape:hover {
            color: #1976d2;
        }

        .btn-icon.delete-etape:hover {
            color: #d32f2f;
        }

        .etape-details {
            color: #666;
        }

        .etape-details p {
            margin: 10px 0;
            line-height: 1.4;
        }

        .etape-meta {
            display: flex;
            gap: 15px;
            font-size: 0.9em;
            color: #888;
        }

        .etape-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
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
        .form-group textarea,
        .form-group select {
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
            .etapes-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 10px;
            }

            .etape-meta {
                flex-direction: column;
                gap: 5px;
            }
        }

        /* Style du bouton d'ajout */
        #add-etape-btn {
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

        #add-etape-btn:hover {
            background-color: #006632;
            }

        #add-etape-btn i {
            font-size: 1.1em;
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
                    <h2>Gestion des Étapes</h2>
                    <button id="add-etape-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter une étape
                    </button>
                </div>

                <div class="data-table-container">
                    <div class="etapes-grid">
                    <?php foreach ($etapes as $etape): ?>
                        <div class="etape-card" data-etape-id="<?php echo $etape['id']; ?>">
                            <div class="etape-header">
                                <div class="etape-info">
                                    <h3><?php echo htmlspecialchars($etape['nom']); ?></h3>
                                        <span class="etape-status <?php echo $etape['statut']; ?>">
                                        <?php 
                                            switch($etape['statut']) {
                                                case 'a_venir':
                                                    echo 'À venir';
                                                break;
                                                case 'en_cours':
                                                    echo 'En cours';
                                                break;
                                                case 'terminee':
                                                echo 'Terminée';
                                                break;
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="etape-actions">
                                    <button class="btn-icon edit-etape" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete-etape" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="etape-details">
                                <p><?php echo htmlspecialchars($etape['description']); ?></p>
                                <div class="etape-meta">
                                    <span class="date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($etape['date_debut'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($etape['date_fin'])); ?>
                                    </span>
                                    <span class="ordre">
                                        <i class="fas fa-sort"></i>
                                        Ordre: <?php echo $etape['ordre']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier une étape -->
    <div id="etape-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter une étape</h3>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="etape-form">
                    <input type="hidden" id="etapeId" name="etapeId">
                    
                    <div class="form-group">
                        <label for="nom">Nom de l'étape</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">Date de début</label>
                            <input type="date" id="date_debut" name="date_debut" required>
                        </div>

                        <div class="form-group">
                            <label for="date_fin">Date de fin</label>
                            <input type="date" id="date_fin" name="date_fin" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ordre">Ordre</label>
                        <input type="number" id="ordre" name="ordre" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" required>
                            <option value="a_venir">À venir</option>
                            <option value="en_cours">En cours</option>
                            <option value="terminee">Terminée</option>
                        </select>
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
        const modal = document.getElementById('etape-modal');
        const addEtapeBtn = document.getElementById('add-etape-btn');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancel-btn');
        const etapeForm = document.getElementById('etape-form');
        const etapesGrid = document.querySelector('.etapes-grid');

        // État de chargement
        let isLoading = false;

        // Fonction pour rafraîchir la liste des étapes
        async function refreshEtapes() {
            try {
                const response = await fetch('etapes.php');
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.querySelector('.etapes-grid');
                if (newGrid) {
                    etapesGrid.innerHTML = newGrid.innerHTML;
                    attachEtapeActions();
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
            const submitBtn = etapeForm.querySelector('button[type="submit"]');
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
        function validateEtapeData(data) {
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

            // Validation des dates
            if (!data.date_debut) {
                errors.push('La date de début est requise');
            }
            if (!data.date_fin) {
                errors.push('La date de fin est requise');
            }
            if (data.date_debut && data.date_fin) {
                const debut = new Date(data.date_debut);
                const fin = new Date(data.date_fin);
                if (debut > fin) {
                    errors.push('La date de début doit être antérieure à la date de fin');
                }
            }

            // Validation de l'ordre
            if (!data.ordre || data.ordre < 1) {
                errors.push('L\'ordre doit être un nombre positif');
            }

            // Validation du statut
            if (!['a_venir', 'en_cours', 'terminee'].includes(data.statut)) {
                errors.push('Le statut est invalide');
            }

            return errors;
        }

        // Fonction pour formater une date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Fonction pour afficher le modal
        function showModal(etape = null) {
            console.log('Affichage du modal avec les données:', etape);
            
            const modal = document.getElementById('etape-modal');
            if (!modal) {
                console.error('Modal non trouvé dans le DOM');
                return;
            }

            if (etape) {
                console.log('Remplissage du formulaire avec les données:', etape);
                document.getElementById('etapeId').value = etape.id;
                document.getElementById('nom').value = etape.nom;
                document.getElementById('description').value = etape.description;
                document.getElementById('date_debut').value = formatDate(etape.date_debut);
                document.getElementById('date_fin').value = formatDate(etape.date_fin);
                document.getElementById('ordre').value = etape.ordre;
                document.getElementById('statut').value = etape.statut;
                document.querySelector('.modal-header h3').textContent = 'Modifier une étape';
            } else {
                console.log('Réinitialisation du formulaire pour nouvelle étape');
                etapeForm.reset();
                document.getElementById('etapeId').value = '';
                document.querySelector('.modal-header h3').textContent = 'Ajouter une étape';
            }

            modal.style.display = 'flex';
            requestAnimationFrame(() => {
            modal.classList.add('show');
            });
        }

        // Fonction pour masquer le modal
        function hideModal() {
            console.log('Masquage du modal');
            const modal = document.getElementById('etape-modal');
            if (!modal) return;

            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            etapeForm.reset();
            }, 300);
        }

        // Fonction pour créer une carte d'étape
        function createEtapeCard(etape) {
            console.log('Création de carte:', etape);
            if (!etape.id) {
                console.error('ID manquant pour la carte:', etape);
                return '';
            }

            const statutText = {
                'en_cours': 'En cours',
                'terminee': 'Terminée',
                'a_venir': 'À venir'
            }[etape.statut];

            return `
                <div class="etape-card" data-etape-id="${etape.id}">
                    <div class="etape-header">
                        <div class="etape-info">
                            <h3>${etape.nom}</h3>
                            <span class="etape-status ${etape.statut}">${statutText}</span>
                        </div>
                        <div class="etape-actions">
                            <button class="btn-icon edit-etape" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon delete-etape" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="etape-details">
                        <p>${etape.description}</p>
                        <div class="etape-meta">
                            <span class="date">
                                <i class="fas fa-calendar"></i>
                                ${formatDate(etape.date_debut)} - ${formatDate(etape.date_fin)}
                            </span>
                            <span class="ordre">
                                <i class="fas fa-sort"></i>
                                Ordre: ${etape.ordre}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }

        // Gestionnaires d'événements
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM chargé, initialisation des événements');
            
            // Bouton d'ajout
            if (addEtapeBtn) {
                addEtapeBtn.addEventListener('click', () => {
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
            if (etapeForm) {
        etapeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
                    console.log('Soumission du formulaire');
                    if (isLoading) return;

            const formData = new FormData(etapeForm);
                    const etapeData = {
                        etapeId: formData.get('etapeId'),
                        nom: formData.get('nom'),
                        description: formData.get('description'),
                        date_debut: formData.get('date_debut'),
                        date_fin: formData.get('date_fin'),
                        ordre: formData.get('ordre'),
                        statut: formData.get('statut')
                    };

                    console.log('Données du formulaire:', etapeData);

                    // Validation
                    const errors = validateEtapeData(etapeData);
                    if (errors.length > 0) {
                        showNotification(errors.join('<br>'), 'error');
                        return;
                    }

                    try {
                        setLoading(true);
                const response = await fetch('actions/save_etape.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(etapeData)
                });

                const data = await response.json();
                        console.log('Réponse du serveur:', data);

                if (data.success) {
                            showNotification(data.message);
                    hideModal();
                            await refreshEtapes();
                } else {
                            showNotification(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                        showNotification('Une erreur est survenue lors de l\'enregistrement de l\'étape', 'error');
                    } finally {
                        setLoading(false);
            }
        });
            }

            // Attacher les événements initialement
            attachEtapeActions();
        });

        // Fonction pour attacher les événements aux cartes
        function attachEtapeActions() {
            console.log('Attachement des événements aux cartes');
            
            // Édition
            document.querySelectorAll('.edit-etape').forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Clic sur le bouton d\'édition');
                    
                    const etapeCard = this.closest('.etape-card');
                    if (!etapeCard) {
                        console.error('Carte d\'étape non trouvée');
                        return;
                    }
                    
                    const etapeId = etapeCard.dataset.etapeId;
                    console.log('ID de l\'étape à modifier:', etapeId);

                    try {
                        setLoading(true);
                        const response = await fetch(`actions/get_etape.php?id=${etapeId}`);
                        const data = await response.json();
                        console.log('Données de l\'étape reçues:', data);
                        
                        if (data.success && data.etape) {
                            console.log('Affichage du modal avec les données:', data.etape);
                            showModal(data.etape);
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
        document.querySelectorAll('.delete-etape').forEach(button => {
            button.addEventListener('click', async function() {
                    console.log('Clic sur le bouton de suppression');
                    if (isLoading) return;
                const etapeCard = this.closest('.etape-card');
                const etapeId = etapeCard.dataset.etapeId;

                if (confirm('Êtes-vous sûr de vouloir supprimer cette étape ?')) {
                    try {
                            setLoading(true);
                        const response = await fetch('actions/delete_etape.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: etapeId })
                        });

                        const data = await response.json();
                            console.log('Réponse de suppression:', data);
                        if (data.success) {
                                showNotification(data.message);
                                await refreshEtapes();
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