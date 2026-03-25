-- Corriger la table api_tokens
-- 1. Augmenter la taille du champ token
ALTER TABLE `api_tokens`
MODIFY COLUMN `token` VARCHAR(255) NOT NULL;

-- 2. Rendre user_id nullable (optionnel, pour tracer qui a créé le token)
ALTER TABLE `api_tokens`
MODIFY COLUMN `user_id` INT(11) NULL;
