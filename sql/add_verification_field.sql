-- Ajout du champ dynamique pour le code de vérification
INSERT INTO dynamic_field_definitions (field_name, field_type, is_required)
VALUES ('Verification Code', 'text', 0);

-- Ajout du champ dynamique pour l'email s'il n'existe pas déjà
INSERT INTO dynamic_field_definitions (field_name, field_type, is_required)
SELECT 'Email', 'email', 1
WHERE NOT EXISTS (
    SELECT 1 FROM dynamic_field_definitions WHERE field_name = 'Email'
); 