-- Ajout de la colonne email à la table public_votes
ALTER TABLE public_votes 
ADD COLUMN email VARCHAR(255) NULL 
AFTER phone_number;
