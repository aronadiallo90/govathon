-- Suppression des anciennes contraintes d'unicité
ALTER TABLE `public_votes`
    DROP INDEX `unique_vote`,
    DROP INDEX `phone_number`,
    DROP INDEX `email`;

-- Ajout des nouvelles contraintes
ALTER TABLE `public_votes`
    -- Permet un même téléphone pour différentes étapes
    ADD CONSTRAINT `unique_phone_per_etape` UNIQUE (`phone_number`, `etape_id`),
    -- Permet un même email pour différentes étapes
    ADD CONSTRAINT `unique_email_per_etape` UNIQUE (`email`, `etape_id`);

-- Modification de la colonne email pour ne pas accepter les doublons entre étapes
ALTER TABLE `public_votes`
    MODIFY COLUMN `email` varchar(255) NOT NULL;
