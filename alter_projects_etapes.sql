-- Ajout de la table project_etapes
CREATE TABLE IF NOT EXISTS `project_etapes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `etape_id` int(11) NOT NULL,
  `status` enum('en_cours','valide','elimine') NOT NULL DEFAULT 'en_cours',
  `score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_etape` (`project_id`,`etape_id`),
  KEY `etape_id` (`etape_id`),
  CONSTRAINT `project_etapes_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_etapes_ibfk_2` FOREIGN KEY (`etape_id`) REFERENCES `etapes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajout du champ score dans la table votes
ALTER TABLE `votes` 
ADD COLUMN `score` decimal(5,2) DEFAULT NULL AFTER `note`;

-- Ajout des index pour optimiser les requêtes
ALTER TABLE `votes` 
ADD INDEX `idx_votes_etape` (`etape_id`),
ADD INDEX `idx_votes_project` (`project_id`);

-- Création du trigger pour mettre à jour project_etapes
DELIMITER //
CREATE TRIGGER after_vote_insert
AFTER INSERT ON votes
FOR EACH ROW
BEGIN
    DECLARE avg_score DECIMAL(5,2);
    
    -- Calculer la moyenne des scores pour ce projet dans cette étape
    SELECT AVG(score) INTO avg_score
    FROM votes
    WHERE project_id = NEW.project_id 
    AND etape_id = NEW.etape_id;
    
    -- Mettre à jour ou insérer dans project_etapes
    INSERT INTO project_etapes (project_id, etape_id, score)
    VALUES (NEW.project_id, NEW.etape_id, avg_score)
    ON DUPLICATE KEY UPDATE score = avg_score;
END//
DELIMITER ;

-- Migration des données existantes
-- Insérer tous les projets dans la première étape
INSERT INTO project_etapes (project_id, etape_id, status)
SELECT p.id, e.id, 'en_cours'
FROM projects p
CROSS JOIN etapes e
WHERE e.ordre = 1
ON DUPLICATE KEY UPDATE status = 'en_cours';

-- Mettre à jour les scores existants
UPDATE project_etapes pe
JOIN (
    SELECT project_id, etape_id, AVG(note) as avg_score
    FROM votes
    GROUP BY project_id, etape_id
) v ON pe.project_id = v.project_id AND pe.etape_id = v.etape_id
SET pe.score = v.avg_score; 