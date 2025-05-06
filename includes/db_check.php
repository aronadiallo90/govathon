<?php
function ensureIconColumnExists($pdo) {
    try {
        $checkColumn = $pdo->query("SHOW COLUMNS FROM secteurs LIKE 'icon'");
        if ($checkColumn->rowCount() === 0) {
            $pdo->exec("
                ALTER TABLE secteurs 
                ADD COLUMN icon VARCHAR(50) DEFAULT 'fa-building' AFTER nom
            ");
        }
        return true;
    } catch (PDOException $e) {
        error_log("Error checking/creating icon column: " . $e->getMessage());
        return false;
    }
}