-- =============================================================================
-- CramCalm Database Schema
-- Import via phpMyAdmin: Import → choose this file → Go
-- Default XAMPP MySQL: user `root`, empty password
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `cramcalm`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `cramcalm`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `heatmap_days`;
DROP TABLE IF EXISTS `focus_sessions`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `ui_flags`;
DROP TABLE IF EXISTS `academic_states`;
DROP TABLE IF EXISTS `personalities`;
DROP TABLE IF EXISTS `user_stats`;
DROP TABLE IF EXISTS `streaks`;
DROP TABLE IF EXISTS `wisdom_trees`;
DROP TABLE IF EXISTS `user_settings`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- users (guest identity via guest_token; email/password ready for later auth)
-- ---------------------------------------------------------------------------
CREATE TABLE `users` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `guest_token`   CHAR(64)        NOT NULL COMMENT 'Session token after login/register',
  `nickname`      VARCHAR(20)     NOT NULL DEFAULT '',
  `email`         VARCHAR(255)    NULL DEFAULT NULL,
  `password_hash` VARCHAR(255)    NULL DEFAULT NULL,
  `onboarded`     TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at`    DATETIME(3)     NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(3),
  `last_login_at` DATETIME(3)     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_guest_token` (`guest_token`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- user_settings (1:1)
-- ---------------------------------------------------------------------------
CREATE TABLE `user_settings` (
  `user_id`             BIGINT UNSIGNED NOT NULL,
  `dark_mode`           TINYINT(1)      NOT NULL DEFAULT 0,
  `theme`               ENUM('auto','light','dark') NOT NULL DEFAULT 'auto',
  `reduced_motion`      TINYINT(1)      NOT NULL DEFAULT 0,
  `notifications`       TINYINT(1)      NOT NULL DEFAULT 1,
  `motivation`          TINYINT(1)      NOT NULL DEFAULT 1,
  `focus_duration`      SMALLINT UNSIGNED NOT NULL DEFAULT 25,
  `break_duration`      SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  `long_break_duration` SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  `font_size`           ENUM('small','normal','large','xlarge') NOT NULL DEFAULT 'normal',
  `accent_color`        CHAR(7)         NULL DEFAULT NULL,
  `greeting_style`      ENUM('casual','formal','fun') NOT NULL DEFAULT 'casual',
  `icon_style`          ENUM('default','minimal','fun') NOT NULL DEFAULT 'default',
  `high_contrast`       TINYINT(1)      NOT NULL DEFAULT 0,
  `dyslexia_font`       TINYINT(1)      NOT NULL DEFAULT 0,
  `large_touches`       TINYINT(1)      NOT NULL DEFAULT 0,
  `screen_reader`       TINYINT(1)      NOT NULL DEFAULT 0,
  `cal_google`          TINYINT(1)      NOT NULL DEFAULT 0,
  `cal_apple`           TINYINT(1)      NOT NULL DEFAULT 0,
  `auto_sync`           TINYINT(1)      NOT NULL DEFAULT 0,
  `bypass_contacts`     JSON            NULL,
  `extra`               JSON            NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_settings_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- tasks
-- ---------------------------------------------------------------------------
CREATE TABLE `tasks` (
  `id`           VARCHAR(40)     NOT NULL,
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `title`        VARCHAR(120)    NOT NULL,
  `description`  VARCHAR(500)    NULL DEFAULT NULL,
  `priority`     ENUM('chill','serious','panic') NOT NULL DEFAULT 'chill',
  `deadline`     DATETIME(3)     NULL DEFAULT NULL,
  `completed`    TINYINT(1)      NOT NULL DEFAULT 0,
  `completed_at` DATETIME(3)     NULL DEFAULT NULL,
  `category`     VARCHAR(32)     NULL DEFAULT 'general',
  `subtasks`     JSON            NULL,
  `created_at`   DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at`   DATETIME(3)     NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`id`),
  KEY `idx_tasks_user` (`user_id`),
  KEY `idx_tasks_user_completed` (`user_id`, `completed`),
  KEY `idx_tasks_user_deadline` (`user_id`, `deadline`),
  KEY `idx_tasks_user_category` (`user_id`, `category`),
  CONSTRAINT `fk_tasks_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- focus_sessions
-- ---------------------------------------------------------------------------
CREATE TABLE `focus_sessions` (
  `id`                 VARCHAR(40)     NOT NULL,
  `user_id`            BIGINT UNSIGNED NOT NULL,
  `task_id`            VARCHAR(40)     NULL DEFAULT NULL,
  `duration_minutes`   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `completed_minutes`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `started_at`         DATETIME(3)     NOT NULL,
  `ended_at`           DATETIME(3)     NOT NULL,
  `completed`          TINYINT(1)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_focus_user` (`user_id`),
  KEY `idx_focus_user_ended` (`user_id`, `ended_at`),
  KEY `idx_focus_task` (`task_id`),
  CONSTRAINT `fk_focus_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_focus_task`
    FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- wisdom_trees (1:1)
-- ---------------------------------------------------------------------------
CREATE TABLE `wisdom_trees` (
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `stage`        TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `xp`           INT UNSIGNED     NOT NULL DEFAULT 0,
  `health`       TINYINT UNSIGNED NOT NULL DEFAULT 100,
  `last_watered` DATETIME(3)      NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_tree_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- streaks (1:1)
-- ---------------------------------------------------------------------------
CREATE TABLE `streaks` (
  `user_id`   BIGINT UNSIGNED NOT NULL,
  `current`   INT UNSIGNED    NOT NULL DEFAULT 0,
  `best`      INT UNSIGNED    NOT NULL DEFAULT 0,
  `last_date` DATE            NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_streak_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- heatmap_days
-- ---------------------------------------------------------------------------
CREATE TABLE `heatmap_days` (
  `user_id`        BIGINT UNSIGNED NOT NULL,
  `day`            DATE            NOT NULL,
  `focus_minutes`  INT UNSIGNED    NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`, `day`),
  CONSTRAINT `fk_heatmap_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- user_stats (1:1 denormalized counters)
-- ---------------------------------------------------------------------------
CREATE TABLE `user_stats` (
  `user_id`               BIGINT UNSIGNED NOT NULL,
  `total_focus_minutes`   INT UNSIGNED    NOT NULL DEFAULT 0,
  `total_tasks_completed` INT UNSIGNED    NOT NULL DEFAULT 0,
  `total_sessions`        INT UNSIGNED    NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_stats_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- personalities (1:1 snapshot)
-- ---------------------------------------------------------------------------
CREATE TABLE `personalities` (
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `type`         VARCHAR(32)     NULL DEFAULT NULL,
  `scores`       JSON            NULL,
  `evaluated_at` DATETIME(3)     NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_personality_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- academic_states (current mood state, 1:1)
-- ---------------------------------------------------------------------------
CREATE TABLE `academic_states` (
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `state`      ENUM('stable','pressure','crisis','burnout') NOT NULL DEFAULT 'stable',
  `updated_at` DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_academic_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- chat_messages (Study Buddy history)
-- ---------------------------------------------------------------------------
CREATE TABLE `chat_messages` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `role`       ENUM('user','ai') NOT NULL,
  `text`       TEXT            NOT NULL,
  `created_at` DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`id`),
  KEY `idx_chat_user_created` (`user_id`, `created_at`),
  CONSTRAINT `fk_chat_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- ui_flags (1:1)
-- ---------------------------------------------------------------------------
CREATE TABLE `ui_flags` (
  `user_id`           BIGINT UNSIGNED NOT NULL,
  `swipe_hint_shown`  TINYINT(1)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_ui_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
