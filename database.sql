-- ============================================================================
-- Infographic CMS - Database Schema
-- ============================================================================
-- Nastavení kódování pro plnou podporu češtiny a emoji
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. Tabulka administrátorů
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Argon2id hash',
  `role` VARCHAR(20) DEFAULT 'admin',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. Tabulka kategorií
-- ============================================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL COMMENT 'Pro URL: moje-kategorie',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. Hlavní tabulka příspěvků (Infografiky)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `image_filename` VARCHAR(255) NOT NULL COMMENT 'Pouze název souboru, např. hash.webp',
  `content` TEXT COMMENT 'Text článku a zdroje (HTML)',
  `meta_description` VARCHAR(160) COMMENT 'Pro SEO',
  `is_published` TINYINT(1) DEFAULT 1,
  `views` INT(11) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_published` (`is_published`),
  KEY `idx_category` (`category_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. Tabulka štítků (Tags)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. Vazební tabulka (M:N) pro příspěvky a štítky
-- ============================================================================
CREATE TABLE IF NOT EXISTS `post_tags` (
  `post_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_tag_id` (`tag_id`),
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. Tabulka pro rate limiting (ochrana proti brute-force)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `failed_logins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) DEFAULT NULL,
  `ip_address` INT UNSIGNED NOT NULL COMMENT 'IP adresa jako INT (INET_ATON)',
  `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`, `attempted_at`),
  KEY `idx_username_time` (`username`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Ukázkové kategorie
-- ============================================================================
INSERT INTO `categories` (`name`, `slug`) VALUES
('Zdraví', 'zdravi'),
('Ekonomika', 'ekonomika'),
('Věda', 'veda'),
('Technologie', 'technologie'),
('Životní prostředí', 'zivotni-prostredi')
ON DUPLICATE KEY UPDATE name=name;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- Konec databázového schématu
-- ============================================================================
