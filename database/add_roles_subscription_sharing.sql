-- ============================================================================
-- SBN v1.0 - Ajout système de rôles, abonnements et partage
-- ============================================================================

-- ============================================================================
-- 1. Modification de la table users - Ajout des nouveaux rôles et abonnements
-- ============================================================================

-- Modifier le type ENUM pour ajouter 'collaborator' et 'client'
ALTER TABLE `users`
MODIFY COLUMN `role` ENUM('admin','user','collaborator','client') NOT NULL DEFAULT 'user';

-- Ajouter les colonnes d'abonnement
ALTER TABLE `users`
ADD COLUMN `subscription_status` ENUM('active','expired','suspended','trial') NOT NULL DEFAULT 'trial' AFTER `role`,
ADD COLUMN `subscription_start` DATE DEFAULT NULL AFTER `subscription_status`,
ADD COLUMN `subscription_end` DATE DEFAULT NULL AFTER `subscription_start`,
ADD COLUMN `subscription_notes` TEXT DEFAULT NULL AFTER `subscription_end`,
ADD COLUMN `free_days_granted` INT DEFAULT 0 AFTER `subscription_notes`;

-- Ajouter les index
ALTER TABLE `users`
ADD INDEX `idx_subscription_status` (`subscription_status`),
ADD INDEX `idx_subscription_end` (`subscription_end`);

-- ============================================================================
-- 2. Création de la table shared_access - Gestion des partages
-- ============================================================================

CREATE TABLE IF NOT EXISTS `shared_access` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `owner_user_id` INT(11) NOT NULL COMMENT 'Utilisateur qui partage',
  `shared_with_user_id` INT(11) NOT NULL COMMENT 'Utilisateur qui reçoit le partage',
  `company_id` INT(11) DEFAULT NULL COMMENT 'Société partagée (NULL = toutes les sociétés)',
  `access_level` ENUM('read','write') NOT NULL DEFAULT 'read',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_shared_with` (`shared_with_user_id`),
  KEY `idx_company` (`company_id`),
  KEY `idx_active` (`is_active`),
  UNIQUE KEY `unique_share` (`owner_user_id`, `shared_with_user_id`, `company_id`),
  CONSTRAINT `fk_shared_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_with` FOREIGN KEY (`shared_with_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. Création de la table nas_devices - Informations des NAS Synology
-- ============================================================================

CREATE TABLE IF NOT EXISTS `nas_devices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `company_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `quickconnect_id` VARCHAR(100) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `model` VARCHAR(50) DEFAULT NULL,
  `dsm_version` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_seen_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_company` (`company_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_nas_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. Modification de la table backup_devices - Lien avec NAS
-- ============================================================================

ALTER TABLE `backup_devices`
ADD COLUMN `nas_device_id` INT(11) DEFAULT NULL AFTER `company_id`,
ADD CONSTRAINT `fk_device_nas` FOREIGN KEY (`nas_device_id`) REFERENCES `nas_devices` (`id`) ON DELETE SET NULL;

-- ============================================================================
-- 5. Création de la table password_reset_history - Historique des réinitialisations
-- ============================================================================

CREATE TABLE IF NOT EXISTS `password_reset_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `reset_by_admin_id` INT(11) DEFAULT NULL COMMENT 'Admin qui a réinitialisé le mot de passe',
  `reset_type` ENUM('self','admin') NOT NULL DEFAULT 'self',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_admin` (`reset_by_admin_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reset_admin` FOREIGN KEY (`reset_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. Mise à jour des utilisateurs existants
-- ============================================================================

-- Mettre l'admin en abonnement actif indéfiniment
UPDATE `users`
SET `subscription_status` = 'active',
    `subscription_start` = CURDATE(),
    `subscription_end` = DATE_ADD(CURDATE(), INTERVAL 100 YEAR)
WHERE `role` = 'admin';

-- Mettre les utilisateurs normaux en trial de 30 jours
UPDATE `users`
SET `subscription_status` = 'trial',
    `subscription_start` = CURDATE(),
    `subscription_end` = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
WHERE `role` = 'user';

-- ============================================================================
-- Fin du script
-- ============================================================================
