<?php
require_once 'includes/db.php';

try {
    // Test database connection
    $stmt = $pdo->query("SELECT 1");
    echo "Database connection successful\n";
    
    // Test sectors table
    $stmt = $pdo->query("SELECT COUNT(*) FROM secteurs");
    $count = $stmt->fetchColumn();
    echo "Number of sectors in database: $count\n";
    
    // Show table structure
    $stmt = $pdo->query("DESCRIBE secteurs");
    echo "\nTable structure:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}