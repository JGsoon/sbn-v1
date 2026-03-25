-- Ajout de la colonne phone à la table users
ALTER TABLE `users`
ADD COLUMN `phone` VARCHAR(20) NULL AFTER `email`;
