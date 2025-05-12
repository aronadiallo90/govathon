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
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content {
            margin: 0;
            padding: 0;
        }

        .etapes-content {
            padding: 1rem;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .content-header h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin: 0;
        }

        .etapes-timeline {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .etape-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .etape-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .etape-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .etape-info h3 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }

        .etape-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .etape-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .etape-status.active {
            background: #d4edda;
            color: #155724;
        }

        .etape-status.completed {
            background: #cce5ff;
            color: #004085;
        }

        .etape-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: #f8f9fa;
            color: #2c3e50;
        }

        .etape-details {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .etape-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .etape-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #00843F;
            color: #fff;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: #006b32;
            transform: translateY(-1px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #00843F;
            box-shadow: 0 0 0 3px rgba(0, 132, 63, 0.1);
            outline: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #e0e0e0;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .etapes-content {
                padding: 1rem;
            }

            .content-header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .etapes-timeline {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .content-header h1 {
                font-size: 1.5rem;
            }

            .etape-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
                        <?php include 'components/header.php'; ?>


            <div class="etapes-content">
                <div class="content-header">
                    <h1>Gestion des Étapes</h1>
                    <button id="add-etape-btn" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter une étape
                    </button>
                </div>

                <div class="etapes-timeline">
                    <?php foreach ($etapes as $etape): ?>
                        <div class="etape-card" data-etape-id="<?php echo $etape['id']; ?>">
                            <div class="etape-header">
                                <div class="etape-info">
                                    <h3><?php echo htmlspecialchars($etape['nom']); ?></h3>
                                    <span class="etape-status <?php echo $etape['etat']; ?>">
                                        <?php 
                                        switch($etape['etat']) {
                                            case 'pending':
                                                echo 'En attente';
                                                break;
                                            case 'active':
                                                echo 'Active';
                                                break;
                                            case 'completed':
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
                        <label for="etat">État</label>
                        <select id="etat" name="etat" required>
                            <option value="pending">En attente</option>
                            <option value="active">Active</option>
                            <option value="completed">Terminée</option>
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
        // Gestion du modal
        const modal = document.getElementById('etape-modal');
        const addEtapeBtn = document.getElementById('add-etape-btn');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancel-btn');
        const etapeForm = document.getElementById('etape-form');

        function showModal(etape = null) {
            if (etape) {
                document.getElementById('etapeId').value = etape.id;
                document.getElementById('nom').value = etape.nom;
                document.getElementById('description').value = etape.description;
                document.getElementById('date_debut').value = etape.date_debut;
                document.getElementById('date_fin').value = etape.date_fin;
                document.getElementById('ordre').value = etape.ordre;
                document.getElementById('etat').value = etape.etat;
                document.querySelector('.modal-header h3').textContent = 'Modifier une étape';
            } else {
                etapeForm.reset();
                document.getElementById('etapeId').value = '';
                document.querySelector('.modal-header h3').textContent = 'Ajouter une étape';
            }
            modal.classList.add('show');
        }

        function hideModal() {
            modal.classList.remove('show');
            etapeForm.reset();
        }

        addEtapeBtn.addEventListener('click', () => showModal());
        closeModalBtn.addEventListener('click', hideModal);
        cancelBtn.addEventListener('click', hideModal);

        // Gestion du formulaire
        etapeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(etapeForm);
            const etapeData = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('actions/save_etape.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(etapeData)
                });

                const data = await response.json();
                if (data.success) {
                    hideModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'enregistrement de l\'étape.');
            }
        });

        // Gestion de la suppression
        document.querySelectorAll('.delete-etape').forEach(button => {
            button.addEventListener('click', async function() {
                const etapeCard = this.closest('.etape-card');
                const etapeId = etapeCard.dataset.etapeId;

                if (confirm('Êtes-vous sûr de vouloir supprimer cette étape ?')) {
                    try {
                        const response = await fetch('actions/delete_etape.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: etapeId })
                        });

                        const data = await response.json();
                        if (data.success) {
                            etapeCard.remove();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la suppression de l\'étape.');
                    }
                }
            });
        });

        // Gestion de l'édition
        document.querySelectorAll('.edit-etape').forEach(button => {
            button.addEventListener('click', async function() {
                const etapeCard = this.closest('.etape-card');
                const etapeId = etapeCard.dataset.etapeId;

                try {
                    const response = await fetch(`actions/get_etape.php?id=${etapeId}`);
                    const data = await response.json();
                    if (data.success) {
                        showModal(data.etape);
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la récupération des données de l\'étape.');
                }
            });
        });

        // Gestion de la recherche
        const searchInput = document.querySelector('.search-bar input');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.etape-card').forEach(card => {
                const nom = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('.etape-details p').textContent.toLowerCase();
                if (nom.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 