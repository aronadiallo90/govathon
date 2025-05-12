<?php
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role, is_active 
        FROM users 
        WHERE email = ? 
        AND is_active = 1
    ");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function isUserActive($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result && $result['is_active'] == 1;
}

function checkUserRole($pdo, $userId, $allowedRoles = ['admin', 'superadmin']) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result && in_array($result['role'], $allowedRoles);
}

function checkAccess($allowedRoles = ['admin', 'superadmin']) {
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        header('Location: unauthorized.php');
        exit;
    }
    
    return true;
}

function isSuperAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin';
}

function isAdmin() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'superadmin']);
}

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= mb_substr($word, 0, 1);
    }
    return mb_strtoupper($initials);
}
