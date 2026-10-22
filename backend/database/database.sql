-- Dump of database/database.sqlite in MySQL / MariaDB format
-- Generated: 2026-10-21 14:16:08
-- Source   : backend/database/database.sqlite
-- Target   : MySQL 5.7+ / 8.x, MariaDB 10.3+
-- Import   : mysql -u root -p <nama_database> < database.sql
--            (or phpMyAdmin > Import > pilih database.sql)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `books`;

CREATE TABLE `books` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255) NOT NULL,
  `isbn` VARCHAR(255) NULL,
  `category` VARCHAR(255) NULL,
  `status` ENUM('available', 'borrowed') NOT NULL DEFAULT 'available',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `books_isbn_unique` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `books` (`id`, `user_id`, `title`, `author`, `isbn`, `category`, `status`, `created_at`, `updated_at`) VALUES
  (1, 1, 'Laskar Pelangi', 'Andrea Hirata', '9789793062792', 'Fiksi', 'borrowed', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (2, 1, 'Bumi Manusia', 'Pramoedya Ananta Toer', '9789799731234', 'Fiksi', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (3, 1, 'Clean Code', 'Robert C. Martin', '9780132350884', 'Teknologi', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (4, 1, 'Sapiens: Riwayat Singkat Umat Manusia', 'Yuval Noah Harari', '9786024246986', 'Sejarah', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (5, 1, 'Filosofi Teras', 'Henry Manampiring', '9786024125189', 'Pengembangan Diri', 'available', '2026-10-21 10:02:07', '2026-10-21 10:04:50'),
  (6, 1, 'Bumi', 'Tere Liye', '9786020330814', 'Fiksi', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (7, 1, 'Perahu Kertas', 'Dee Lestari', '9786020334898', 'Fiksi', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (8, 1, 'Hujan', 'Tere Liye', '9786020825945', 'Fiksi', 'borrowed', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (9, 1, 'Refactoring: Improving the Design of Existing Code', 'Martin Fowler', '9780134757599', 'Teknologi', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (10, 1, 'The Pragmatic Programmer', 'Andrew Hunt & David Thomas', '9780135957059', 'Teknologi', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (11, 1, 'Algoritma dan Pemrograman', 'Rinaldi Munir', '9786021516648', 'Teknologi', 'borrowed', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (12, 1, 'Sejarah Indonesia Modern', 'M. C. Ricklefs', '9789794616697', 'Sejarah', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (13, 1, 'Guns, Germs, and Steel', 'Jared Diamond', '9789793062900', 'Sejarah', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (14, 1, 'Atomic Habits', 'James Clear', '9786020633175', 'Pengembangan Diri', 'borrowed', '2026-10-21 10:02:07', '2026-10-21 10:02:07'),
  (15, 1, 'Berpikir Cepat dan Lambat', 'Daniel Kahneman', '9786024246948', 'Pengembangan Diri', 'available', '2026-10-21 10:02:07', '2026-10-21 10:02:07');

ALTER TABLE `books` AUTO_INCREMENT = 16;

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` TEXT NOT NULL,
  `exception` TEXT NOT NULL,
  `failed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` TEXT NOT NULL,
  `options` TEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` TEXT NOT NULL,
  `attempts` INT NOT NULL,
  `reserved_at` INT NULL,
  `available_at` INT NOT NULL,
  `created_at` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
  (1, '0001_01_01_000000_create_users_table', 1),
  (2, '0001_01_01_000001_create_cache_table', 1),
  (3, '0001_01_01_000002_create_jobs_table', 1),
  (4, '2026_10_21_100008_create_personal_access_tokens_table', 1),
  (5, '2026_10_21_100100_create_books_table', 1);

ALTER TABLE `migrations` AUTO_INCREMENT = 6;

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `personal_access_tokens`;

CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` INT NOT NULL,
  `name` TEXT NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
  (3, 'App\\Models\\User', 1, 'api-client', 'cbfdd952c79372dedaaf8f87c8541646aafe5edbe94075a56908cfcf1d52200b', '[\"*\"]', '2026-10-21 10:04:50', NULL, '2026-10-21 10:04:49', '2026-10-21 10:04:50'),
  (4, 'App\\Models\\User', 1, 'api-client', '7b662fbe465bea6fe9e9cdfaf55c4bb361e546ba230c3c114db64588f11d9d4e', '[\"*\"]', '2026-10-21 10:05:33', NULL, '2026-10-21 10:05:32', '2026-10-21 10:05:33'),
  (6, 'App\\Models\\User', 1, 'postman', '8de21ccdf38b156a19b0f61e516fd5acaa61cbcf7ba0cd63a342294f78dad8c9', '[\"*\"]', '2026-10-21 10:26:37', NULL, '2026-10-21 10:26:23', '2026-10-21 10:26:37'),
  (10, 'App\\Models\\User', 1, 'api-client', '1ba0495cc26547ce54a50aae3dad4f8495d98c73458c748607dd43e33639b7ed', '[\"*\"]', '2026-10-21 10:50:51', NULL, '2026-10-21 10:50:46', '2026-10-21 10:50:51'),
  (20, 'App\\Models\\User', 1, 'api-client', '7f673f7d70347687daf9ad9321765c7c82134dcff821eec63073b51d50d97eda', '[\"*\"]', '2026-10-21 11:06:25', NULL, '2026-10-21 11:06:23', '2026-10-21 11:06:25'),
  (21, 'App\\Models\\User', 1, 'api-client', '0dbdaaff2de61758e3310e13ef5616848c20f98c2bdd0a1ed0ceb25fc7f78906', '[\"*\"]', '2026-10-21 11:40:43', NULL, '2026-10-21 11:17:24', '2026-10-21 11:40:43'),
  (22, 'App\\Models\\User', 1, 'api-client', 'c3611f3f4f85076b80ada97b8c10746c327d5e7af1945511bb4d10771b6881aa', '[\"*\"]', '2026-10-21 11:26:24', NULL, '2026-10-21 11:26:23', '2026-10-21 11:26:24'),
  (23, 'App\\Models\\User', 1, 'api-client', '9c03dd120ed78f4522db54b3d13fd4df66467ecfa3efd653469e95d5bd8a0a28', '[\"*\"]', '2026-10-21 11:29:11', NULL, '2026-10-21 11:29:06', '2026-10-21 11:29:11'),
  (24, 'App\\Models\\User', 1, 'api-client', 'dd2fd0a97f405c948fdbd388955390426e512d7c8c26a1bac2709efb8aae8689', '[\"*\"]', '2026-10-21 11:30:12', NULL, '2026-10-21 11:30:06', '2026-10-21 11:30:12'),
  (25, 'App\\Models\\User', 1, 'api-client', '2e830c9e4240df17c31108ac1e38e2c341386d4aced6f80002904ba938f5c9a6', '[\"*\"]', '2026-10-21 11:30:56', NULL, '2026-10-21 11:30:50', '2026-10-21 11:30:56'),
  (26, 'App\\Models\\User', 1, 'api-client', '8d4b7a0f0f7cf8ba61eb7d33bd0d9589af6a03c528c3ed5a8ba6b164bd8efbd1', '[\"*\"]', '2026-10-21 11:31:20', NULL, '2026-10-21 11:31:14', '2026-10-21 11:31:20'),
  (28, 'App\\Models\\User', 1, 'api-client', 'd1c578cd5bfeae9d7664dcb93775cfe3510cc40779c3c02e3ddaf84a5c8ae7c1', '[\"*\"]', '2026-10-21 11:33:28', NULL, '2026-10-21 11:32:12', '2026-10-21 11:33:28'),
  (29, 'App\\Models\\User', 1, 'api-client', '535c13aa15dd6dd25009abbfb2017cd9b0bcaefaa1b47e9f60c87ce132e56264', '[\"*\"]', '2026-10-21 11:33:30', NULL, '2026-10-21 11:33:29', '2026-10-21 11:33:30'),
  (30, 'App\\Models\\User', 1, 'api-client', 'cbc3f95c41c9c384cf0d2e23942a710193b4416b24966299d203d06795b49676', '[\"*\"]', '2026-10-21 11:41:47', NULL, '2026-10-21 11:41:46', '2026-10-21 11:41:47');

ALTER TABLE `personal_access_tokens` AUTO_INCREMENT = 31;

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT NULL,
  `ip_address` VARCHAR(255) NULL,
  `user_agent` TEXT NULL,
  `payload` TEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  KEY `sessions_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` DATETIME NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(255) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
  (1, 'Demo Librarian', 'demo@library.test', NULL, '$2y$12$tJNVZVFfA0.GbS1g5KlTSOY0rNO.7E5lUCadlmLDhc/86RR/ZQBlm', NULL, '2026-10-21 10:02:07', '2026-10-21 10:02:07');

ALTER TABLE `users` AUTO_INCREMENT = 2;

ALTER TABLE `books` ADD CONSTRAINT `fk_books_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
