-- ============================================================================
-- SBN v1.0 - Table de partage d'accès
--
-- Cette table permet aux utilisateurs de partager l'accès à certains
-- appareils/sauvegardes avec d'autres utilisateurs par email
--
-- @package SBN
-- @version 1.0.0
-- ============================================================================

CREATE TABLE IF NOT EXISTS `shared_access` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `owner_company_id` INT NOT NULL COMMENT 'Société qui partage',
    `owner_user_id` INT NOT NULL COMMENT 'Utilisateur qui a créé le partage',
    `shared_with_email` VARCHAR(255) NOT NULL COMMENT 'Email de l''invité',
    `shared_with_user_id` INT NULL COMMENT 'User ID si l''invité est inscrit',
    `scope` JSON NOT NULL COMMENT 'Définit ce qui est partagé (device_ids, etc.)',
    `permissions` JSON NOT NULL COMMENT 'Permissions accordées (read, write)',
    `is_active` BOOLEAN DEFAULT 1 COMMENT 'Partage actif ou révoqué',
    `expires_at` DATETIME NULL COMMENT 'Date d''expiration optionnelle',
    `created_at` DATETIME NOT NULL,
    `revoked_at` DATETIME NULL,
    INDEX `idx_owner_company` (`owner_company_id`),
    INDEX `idx_shared_email` (`shared_with_email`),
    INDEX `idx_shared_user` (`shared_with_user_id`),
    INDEX `idx_active` (`is_active`),
    FOREIGN KEY (`owner_company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shared_with_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestion du partage d''accès entre utilisateurs';

-- Index pour rechercher les partages d'un utilisateur invité
CREATE INDEX `idx_shared_active_email` ON `shared_access`(`shared_with_email`, `is_active`);

-- Index pour rechercher les partages créés par une société
CREATE INDEX `idx_owner_active` ON `shared_access`(`owner_company_id`, `is_active`);
