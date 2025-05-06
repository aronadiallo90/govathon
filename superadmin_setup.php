<?php
require_once 'config.php';

// Créer un superadmin avec tous les droits
$name = 'Super Admin';
$email = 'aronadiallo90@gmail.com';
$password = 'P@sser1234'; // Mot de passe demandé
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si le superadmin existe déjà
$stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "Le superadmin existe déjà.\n";
} else {
    // Insérer le superadmin avec secteur_id NULL (tous les droits)
    $stmt = $pdo->prepare('INSERT INTO admins (name, email, password, secteur_id) VALUES (?, ?, ?, NULL)');
    if ($stmt->execute([$name, $email, $hashed_password])) {
        echo "Superadmin créé avec succès.\n";
        echo "Email: $email\n";
        echo "Mot de passe: $password\n";

        // Créer un fichier pour se souvenir du compte
        $file_content = "Superadmin\nEmail: $email\nMot de passe: $password\n";
        $file_path = __DIR__ . DIRECTORY_SEPARATOR . 'superadmin_credentials.txt';
        file_put_contents($file_path, $file_content);
        echo "Fichier superadmin_credentials.txt créé pour mémoriser les identifiants.\n";
    } else {
        echo "Erreur lors de la création du superadmin.\n";
    }
}
?>
