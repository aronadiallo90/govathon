-- Étape 1 : Ajouter une colonne temporaire
ALTER TABLE etapes ADD COLUMN statut_temp VARCHAR(20) NOT NULL DEFAULT 'a_venir';

-- Étape 2 : Copier et convertir les données
UPDATE etapes SET statut_temp = 
    CASE etat
        WHEN 'pending' THEN 'a_venir'
        WHEN 'active' THEN 'en_cours'
        WHEN 'completed' THEN 'terminee'
    END;

-- Étape 3 : Supprimer l'ancienne colonne
ALTER TABLE etapes DROP COLUMN etat;

-- Étape 4 : Renommer la colonne temporaire et définir le type ENUM
ALTER TABLE etapes CHANGE COLUMN statut_temp statut ENUM('a_venir', 'en_cours', 'terminee') NOT NULL DEFAULT 'a_venir'; 