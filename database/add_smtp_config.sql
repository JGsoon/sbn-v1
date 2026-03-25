-- Configuration SMTP par société
CREATE TABLE IF NOT EXISTS `smtp_config` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `company_id` INT NOT NULL UNIQUE,
    `smtp_host` VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com',
    `smtp_port` INT NOT NULL DEFAULT 587,
    `smtp_username` VARCHAR(255) NOT NULL,
    `smtp_password` VARCHAR(255) NOT NULL COMMENT 'Crypté',
    `smtp_from_email` VARCHAR(255) NOT NULL,
    `smtp_from_name` VARCHAR(255) NOT NULL,
    `smtp_encryption` ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    `is_active` BOOLEAN DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
