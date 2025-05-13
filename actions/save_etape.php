<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

try {
    // Récupérer les données JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation des données requises
    $requiredFields = ['nom', 'description', 'date_debut', 'date_fin', 'ordre', 'statut'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Le champ '$field' est requis");
        }
    }

    // Validation des dates
    $dateDebut = strtotime($data['date_debut']);
    $dateFin = strtotime($data['date_fin']);
    
    if ($dateDebut === false || $dateFin === false) {
        throw new Exception('Format de date invalide');
    }
    
    if ($dateDebut > $dateFin) {
        throw new Exception('La date de début doit être antérieure à la date de fin');
    }

    // Validation de l'ordre
    if (!is_numeric($data['ordre']) || $data['ordre'] < 1) {
        throw new Exception('L\'ordre doit être un nombre positif');
    }

    // Validation du statut
    $validStatuses = ['a_venir', 'en_cours', 'terminee'];
    if (!in_array($data['statut'], $validStatuses)) {
        throw new Exception('Statut invalide');
    }

    // Préparation des données
    $etapeData = [
        'nom' => trim($data['nom']),
        'description' => trim($data['description']),
        'date_debut' => date('Y-m-d', $dateDebut),
        'date_fin' => date('Y-m-d', $dateFin),
        'ordre' => intval($data['ordre']),
        'statut' => $data['statut']
    ];

    // Démarrer une transaction
    $pdo->beginTransaction();

    if (!empty($data['etapeId'])) {
        // Mise à jour d'une étape existante
        $stmt = $pdo->prepare("
            UPDATE etapes 
            SET nom = :nom,
                description = :description,
                date_debut = :date_debut,
                date_fin = :date_fin,
                ordre = :ordre,
                statut = :statut,
                updated_at = NOW()
            WHERE id = :id
        ");
        $etapeData['id'] = intval($data['etapeId']);
        $stmt->execute($etapeData);
        $etapeId = $etapeData['id'];
    } else {
        // Création d'une nouvelle étape
        $stmt = $pdo->prepare("
            INSERT INTO etapes (
                nom, 
                description, 
                date_debut, 
                date_fin, 
                ordre, 
                statut, 
                created_at, 
                updated_at
            ) VALUES (
                :nom,
                :description,
                :date_debut,
                :date_fin,
                :ordre,
                :statut,
                NOW(),
                NOW()
            )
        ");
        $stmt->execute($etapeData);
        $etapeId = $pdo->lastInsertId();
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => !empty($data['etapeId']) ? 'Étape mise à jour avec succès' : 'Étape créée avec succès',
        'etapeId' => $etapeId
    ]);

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 