-- Database setup for the PHP Login System with Admin Approval.
-- Structure only: this file contains NO user data.
-- Run it in phpMyAdmin (SQL tab) or the MySQL command line.
-- It uses a NEW database name, so it will not touch any older database you have.

CREATE DATABASE IF NOT EXISTS `php_login_system`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `php_login_system`;

CREATE TABLE IF NOT EXISTS `users` (
  `id`              int(11)      NOT NULL AUTO_INCREMENT,
  `username`        varchar(30)  NOT NULL,
  -- A one-way hash made by PHP's password_hash(). The real password is never stored.
  `password_hash`   varchar(255) NOT NULL,
  `role`            enum('user','admin') NOT NULL DEFAULT 'user',
  `status`          enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  -- Used to slow down password guessing (5 wrong tries = locked for 5 minutes).
  `failed_attempts` int(11)      NOT NULL DEFAULT 0,
  `locked_until`    datetime     DEFAULT NULL,
  `created_at`      timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  -- No two users can share a username (and "Admin" and "admin" count as the same).
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
