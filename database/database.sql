-- Database dump placeholder
-- Create your tables here

-- MySQL schema for Agnes personal website
-- Update username/password before running on production

DROP DATABASE IF EXISTS `agnes_personal_website`;
CREATE DATABASE `agnes_personal_website` DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE `agnes_personal_website`;

-- Admins / users for admin panel
CREATE TABLE `admins` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(100) NOT NULL UNIQUE,
	`password_hash` VARCHAR(255) NOT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lecturers
CREATE TABLE `lecturers` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255),
	`bio` TEXT,
	`photo_path` VARCHAR(255),
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Publications
CREATE TABLE `publications` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(500) NOT NULL,
	`abstract` TEXT,
	`file_path` VARCHAR(255),
	`published_at` DATE,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Consultancy entries
CREATE TABLE `consultancy` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NOT NULL,
	`description` TEXT,
	`file_path` VARCHAR(255),
	`dates` VARCHAR(255),
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages
CREATE TABLE `contacts` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`message` TEXT NOT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example: create a default admin (password is 'changeme' — replace with strong password)
-- Use PHP's password_hash() to generate the hash. Example in PHP: password_hash('yourpassword', PASSWORD_DEFAULT)
-- INSERT INTO `admins` (`username`, `password_hash`) VALUES ('admin', 'REPLACE_WITH_HASH');

