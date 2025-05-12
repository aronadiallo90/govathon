-- Mise à jour de la table etapes
ALTER TABLE `etapes`
ADD COLUMN `date_debut` date NOT NULL AFTER `ordre`,
ADD COLUMN `date_fin` date NOT NULL AFTER `date_debut`,
ADD COLUMN `etat` enum('pending','active','completed') NOT NULL DEFAULT 'pending' AFTER `date_fin`;

-- Mise à jour des données existantes avec des dates par défaut
UPDATE `etapes` SET 
`date_debut` = '2024-05-01',
`date_fin` = '2024-05-15',
`etat` = 'pending'
WHERE `ordre` = 1;

UPDATE `etapes` SET 
`date_debut` = '2024-05-16',
`date_fin` = '2024-05-31',
`etat` = 'pending'
WHERE `ordre` = 2;

UPDATE `etapes` SET 
`date_debut` = '2024-06-01',
`date_fin` = '2024-06-15',
`etat` = 'pending'
WHERE `ordre` = 3;

UPDATE `etapes` SET 
`date_debut` = '2024-06-16',
`date_fin` = '2024-06-30',
`etat` = 'pending'
WHERE `ordre` = 4; 