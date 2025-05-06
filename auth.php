<?php
// Suppression de session_start() ici pour éviter les erreurs de headers déjà envoyés

function isSuperAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin']['is_superadmin'] == 1;
}

function isAdmin() {
    return isset($_SESSION['admin']);
}

function isUser() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isAdmin() && !isUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: unauthorized.php');
        exit;
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header('Location: unauthorized.php');
        exit;
    }
}
?>
