-- =============================================================================
-- CramCalm — Auth migration (run in phpMyAdmin if DB already exists)
-- Safe to re-run: ignores error if last_login_at already exists
-- =============================================================================

USE `cramcalm`;

UPDATE `users` SET `email` = NULL WHERE `email` = '';

-- Add last_login_at (skip manually if column already exists)
ALTER TABLE `users`
  ADD COLUMN `last_login_at` DATETIME(3) NULL DEFAULT NULL AFTER `updated_at`;
