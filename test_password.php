<?php
require_once 'includes/db.php';

$email = 'aronadiallo90@gmail.com';
$password_to_test = 'P@sser1234';

try {
    // Get user info
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role, is_active 
        FROM users 
        WHERE email = ? AND is_active = 1
    ");
    
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password_to_test, $user['password'])) {
        echo "✅ Mot de passe correct pour {$user['email']}\n";
        echo "Role: {$user['role']}\n";
    } else {
        echo "❌ Mot de passe incorrect ou utilisateur non trouvé\n";
        
        // Pour debug uniquement
        echo "Hash stocké: " . ($user ? $user['password'] : 'utilisateur non trouvé') . "\n";
        echo "Nouveau hash du mot de passe testé: " . password_hash($password_to_test, PASSWORD_DEFAULT) . "\n";
    }
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}