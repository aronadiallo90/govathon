<?php
session_start();
require_once 'includes/db.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}

// Récupération des critères
$stmt = $pdo->query("SELECT * FROM criteres ORDER BY id ASC");
$criteres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des étapes
$stmt = $pdo->query("SELECT * FROM etapes ORDER BY ordre ASC");
$etapes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de l'étape active
$stmt = $pdo->query("SELECT * FROM etapes WHERE etat = 'active' LIMIT 1");
$etapeActive = $stmt->fetch(PDO::FETCH_ASSOC);

// Si aucune étape n'est active, rediriger vers un message d'information
if (!$etapeActive) {
    header('Location: no_active_etape.php');
    exit;
}

// Récupération des projets
$stmt = $pdo->query("SELECT p.*, s.nom as secteur_nom 
                     FROM projects p 
                     LEFT JOIN secteurs s ON p.secteur_id = s.id 
                     WHERE p.status = 'submitted'
                     ORDER BY p.id ASC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des votes existants pour l'utilisateur courant
$stmt = $pdo->prepare("
    SELECT v.*, c.nom as critere_nom 
    FROM votes v 
    JOIN criteres c ON v.critere_id = c.id 
    WHERE v.user_id = ? AND v.etape_id = ?
");
$stmt->execute([$_SESSION['user_id'], $etapeActive['id']]);
$votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser les votes par projet et critère
$votesByProject = [];
foreach ($votes as $vote) {
    if (!isset($votesByProject[$vote['project_id']])) {
        $votesByProject[$vote['project_id']] = [];
    }
    $votesByProject[$vote['project_id']][$vote['critere_id']] = $vote['note'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Votes - GOVATHON</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/votes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'components/navbar.php'; ?>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <button id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un projet...">
                    </div>
                    <div class="user-info">
                        <i class="fas fa-bell"></i>
                        <div class="user-profile">
                            <img src="https://via.placeholder.com/40" alt="Profile">
                            <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="votes-content">
                <div class="votes-header">
                    <div class="etape-info">
                        <h2>Gestion des Votes</h2>
                        <div class="current-etape">
                            <span class="etape-badge">Étape active : <?php echo htmlspecialchars($etapeActive['nom']); ?></span>
                            <span class="etape-dates">
                                <?php echo date('d/m/Y', strtotime($etapeActive['date_debut'])); ?> - 
                                <?php echo date('d/m/Y', strtotime($etapeActive['date_fin'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="filters">
                        <select id="secteurFilter">
                            <option value="">Tous les secteurs</option>
                            <?php
                            $secteurs = array_unique(array_column($projects, 'secteur_nom'));
                            foreach ($secteurs as $secteur) {
                                echo '<option value="' . htmlspecialchars($secteur) . '">' . htmlspecialchars($secteur) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="projects-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card" data-project-id="<?php echo $project['id']; ?>">
                            <div class="project-header">
                                <h3><?php echo htmlspecialchars($project['nom']); ?></h3>
                                <span class="secteur-badge"><?php echo htmlspecialchars($project['secteur_nom']); ?></span>
                            </div>
                            <div class="project-description">
                                <?php echo htmlspecialchars(substr($project['description'], 0, 150)) . '...'; ?>
                            </div>
                            <div class="criteria-votes">
                                <?php foreach ($criteres as $critere): ?>
                                    <div class="criteria-group">
                                        <label><?php echo htmlspecialchars($critere['nom']); ?></label>
                                        <div class="vote-slider">
                                            <input type="range" 
                                                   min="0" 
                                                   max="10" 
                                                   step="0.5" 
                                                   value="<?php echo $votesByProject[$project['id']][$critere['id']] ?? 0; ?>"
                                                   data-critere-id="<?php echo $critere['id']; ?>"
                                                   class="vote-input">
                                            <span class="vote-value"><?php echo $votesByProject[$project['id']][$critere['id']] ?? 0; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="project-actions">
                                <button class="btn-primary save-votes" data-project-id="<?php echo $project['id']; ?>">
                                    Enregistrer les votes
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Données globales
        const currentUserId = <?php echo $_SESSION['user_id'] ?? 0; ?>;
        const currentEtapeId = <?php echo $etapeActive['id']; ?>;
        
        // Gestion des curseurs de vote
        document.querySelectorAll('.vote-input').forEach(slider => {
            slider.addEventListener('input', function() {
                this.nextElementSibling.textContent = this.value;
            });
        });

        // Sauvegarde des votes
        document.querySelectorAll('.save-votes').forEach(button => {
            button.addEventListener('click', async function() {
                const projectId = this.dataset.projectId;
                const projectCard = this.closest('.project-card');
                const votes = [];

                projectCard.querySelectorAll('.vote-input').forEach(input => {
                    if (input.value > 0) {
                        votes.push({
                            critere_id: input.dataset.critereId,
                            note: parseFloat(input.value)
                        });
                    }
                });

                if (votes.length === 0) {
                    alert('Veuillez attribuer au moins une note avant de sauvegarder.');
                    return;
                }

                try {
                    const response = await fetch('actions/save_vote.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            project_id: projectId,
                            etape_id: currentEtapeId,
                            votes: votes
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert('Votes enregistrés avec succès !');
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la sauvegarde des votes.');
                }
            });
        });

        // Filtre par secteur
        document.getElementById('secteurFilter').addEventListener('change', function() {
            const selectedSecteur = this.value.toLowerCase();
            document.querySelectorAll('.project-card').forEach(card => {
                const secteur = card.querySelector('.secteur-badge').textContent.toLowerCase();
                card.style.display = !selectedSecteur || secteur === selectedSecteur ? 'block' : 'none';
            });
        });

        // Recherche de projets
        document.querySelector('.search-bar input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.project-card').forEach(card => {
                const projectName = card.querySelector('h3').textContent.toLowerCase();
                const projectDesc = card.querySelector('.project-description').textContent.toLowerCase();
                card.style.display = projectName.includes(searchTerm) || projectDesc.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
