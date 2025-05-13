<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Vérification de l'authentification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    // Récupération des données JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Validation des données requises
    $requiredFields = ['nom', 'description', 'coefficient'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Le champ '$field' est requis");
        }
    }

    // Validation du coefficient
    if (!is_numeric($data['coefficient']) || $data['coefficient'] < 1 || $data['coefficient'] > 5) {
        throw new Exception('Le coefficient doit être un nombre entre 1 et 5');
    }

    // Préparation des données
    $critereData = [
        'nom' => trim($data['nom']),
        'description' => trim($data['description']),
        'coefficient' => floatval($data['coefficient'])
    ];

    // Démarrer une transaction
    $pdo->beginTransaction();

    if (!empty($data['critereId'])) {
        // Mise à jour d'un critère existant
        $stmt = $pdo->prepare("
            UPDATE criteres 
            SET nom = :nom,
                description = :description,
                coefficient = :coefficient,
                updated_at = NOW()
            WHERE id = :id
        ");
        $critereData['id'] = intval($data['critereId']);
    } else {
        // Création d'un nouveau critère
        $stmt = $pdo->prepare("
            INSERT INTO criteres (
                nom, 
                description, 
                coefficient, 
                created_at, 
                updated_at
            ) VALUES (
                :nom,
                :description,
                :coefficient,
                NOW(),
                NOW()
            )
        ");
    }

    $stmt->execute($critereData);

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => !empty($data['critereId']) ? 'Critère mis à jour avec succès' : 'Critère créé avec succès'
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