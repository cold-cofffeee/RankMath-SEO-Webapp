-- RankMath SEO Webapp Database Schema
-- MySQL/MariaDB compatible

-- Users table (for multi-user support if needed in future)
CREATE TABLE IF NOT EXISTS `rm_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects/Websites table
CREATE TABLE IF NOT EXISTS `rm_projects` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `settings` longtext DEFAULT NULL COMMENT 'JSON',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `url` (`url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO Analysis Results
CREATE TABLE IF NOT EXISTS `rm_seo_analysis` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `analysis_type` enum('site','competitor') NOT NULL DEFAULT 'site',
  `score` int(11) DEFAULT NULL,
  `results` longtext DEFAULT NULL COMMENT 'JSON',
  `analyzed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `url` (`url`(191)),
  KEY `analyzed_at` (`analyzed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics data (keywords, impressions, clicks)
CREATE TABLE IF NOT EXISTS `rm_analytics_keywords` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `keyword` varchar(500) NOT NULL,
  `impressions` bigint(20) DEFAULT 0,
  `clicks` bigint(20) DEFAULT 0,
  `position` decimal(5,2) DEFAULT NULL,
  `ctr` decimal(5,2) DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `keyword` (`keyword`(191)),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics pages
CREATE TABLE IF NOT EXISTS `rm_analytics_pages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `url` varchar(1000) NOT NULL,
  `pageviews` bigint(20) DEFAULT 0,
  `unique_visitors` bigint(20) DEFAULT 0,
  `impressions` bigint(20) DEFAULT 0,
  `clicks` bigint(20) DEFAULT 0,
  `position` decimal(5,2) DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `url` (`url`(191)),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 404 Monitor
CREATE TABLE IF NOT EXISTS `rm_404_monitor` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uri` varchar(1000) NOT NULL,
  `referer` varchar(1000) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `hits` int(11) DEFAULT 1,
  `last_accessed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `uri` (`uri`(191)),
  KEY `last_accessed` (`last_accessed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Redirections
CREATE TABLE IF NOT EXISTS `rm_redirections` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_url` varchar(1000) NOT NULL,
  `target_url` varchar(1000) NOT NULL,
  `redirect_type` enum('301','302','307','308','410','451') NOT NULL DEFAULT '301',
  `hits` bigint(20) DEFAULT 0,
  `last_accessed` datetime DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `source_url` (`source_url`(191)),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Local SEO Locations
CREATE TABLE IF NOT EXISTS `rm_local_locations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(500) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `business_hours` text DEFAULT NULL COMMENT 'JSON',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sitemaps
CREATE TABLE IF NOT EXISTS `rm_sitemaps` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('general','news','video','image') NOT NULL DEFAULT 'general',
  `url` varchar(1000) NOT NULL,
  `priority` decimal(2,1) DEFAULT 0.5,
  `changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT 'weekly',
  `last_modified` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schema/Structured Data
CREATE TABLE IF NOT EXISTS `rm_schema` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `url` varchar(1000) NOT NULL,
  `schema_type` varchar(100) NOT NULL,
  `schema_data` longtext NOT NULL COMMENT 'JSON-LD',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `url` (`url`(191)),
  KEY `schema_type` (`schema_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Content AI History
CREATE TABLE IF NOT EXISTS `rm_content_ai` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `keyword` varchar(500) NOT NULL,
  `content_type` varchar(50) DEFAULT NULL,
  `prompt` text DEFAULT NULL,
  `generated_content` longtext DEFAULT NULL,
  `credits_used` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Image SEO Data
CREATE TABLE IF NOT EXISTS `rm_image_seo` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_url` varchar(1000) NOT NULL,
  `alt_text` varchar(500) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `optimized` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `image_url` (`image_url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Application Settings
CREATE TABLE IF NOT EXISTS `rm_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(191) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `autoload` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `rm_settings` (`setting_key`, `setting_value`, `autoload`) VALUES
('app_installed', '1', 1),
('app_version', '1.0.0', 1),
('install_date', NOW(), 1);
