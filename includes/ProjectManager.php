<?php
class ProjectManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function preselectProject($projectId) {
        $this->pdo->beginTransaction();
        try {
            // Vérifier que le projet est en étape Inscription
            $stmt = $this->pdo->prepare("
                SELECT pe.id 
                FROM project_etapes pe
                JOIN etapes e ON pe.etape_id = e.id
                WHERE pe.project_id = ? AND e.nom = 'Inscription'
            ");
            $stmt->execute([$projectId]);
            if (!$stmt->fetch()) {
                throw new Exception("Le projet n'est pas en étape d'inscription");
            }

            // Récupérer l'ID de l'étape Présélection
            $stmt = $this->pdo->prepare("SELECT id FROM etapes WHERE nom = 'Présélection'");
            $stmt->execute();
            $preselectionId = $stmt->fetchColumn();

            // Ajouter le projet à l'étape Présélection
            $stmt = $this->pdo->prepare("
                INSERT INTO project_etapes (project_id, etape_id, status)
                VALUES (?, ?, 'en_cours')
            ");
            $stmt->execute([$projectId, $preselectionId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}