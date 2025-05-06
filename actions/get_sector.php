<?php
session_start();
require_once '../includes/db.php';

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

header('Content-Type: application/json');

// Récupérer l'ID depuis la requête
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID manquant'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM secteurs WHERE id = ?");
    $stmt->execute([$id]);
    $sector = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sector) {
        echo json_encode([
            'success' => true,
            'sector' => $sector
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Secteur non trouvé'
        ]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement du secteur'
    ]);
}