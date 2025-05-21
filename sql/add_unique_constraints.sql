-- Supprimer l'ancienne contrainte unique
ALTER TABLE public_votes
DROP INDEX unique_vote;

-- Ajouter une nouvelle contrainte unique pour phone_number
ALTER TABLE public_votes
ADD CONSTRAINT unique_phone UNIQUE (phone_number);

-- Ajouter une contrainte unique pour email (seulement pour les emails non-null)
ALTER TABLE public_votes
ADD CONSTRAINT unique_email UNIQUE (email);

-- Ajouter une contrainte pour s'assurer qu'un utilisateur ne vote qu'une fois par étape
-- (en utilisant soit le téléphone soit l'email)
ALTER TABLE public_votes
ADD CONSTRAINT unique_vote_per_etape UNIQUE (etape_id, phone_number),
ADD CONSTRAINT unique_email_vote_per_etape UNIQUE (etape_id, email);
