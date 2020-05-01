/**
 * Zugzwang Project
 * SQL for installation of registrations module
 *
 * http://www.zugzwang.org/modules/registrations
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `usergroups`;
CREATE TABLE `usergroups` (
  `usergroup_id` int unsigned NOT NULL AUTO_INCREMENT,
  `usergroup` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usergroup_category_id` int unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sequence` tinyint unsigned DEFAULT NULL,
  `active` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `parameters` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_update` timestamp NOT NULL,
  PRIMARY KEY (`usergroup_id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `usergroup_category_id` (`usergroup_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("Usergroups", NULL, NULL, "usergroups", NULL, NULL, NOW());

DROP TABLE IF EXISTS `participations`;
CREATE TABLE `participations` (
  `participation_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `usergroup_id` int unsigned NOT NULL,
  `date_begin` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `status_category_id` int unsigned NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `verification_hash` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` timestamp NOT NULL,
  PRIMARY KEY (`participation_id`),
  UNIQUE KEY `verification_hash` (`verification_hash`),
  KEY `contact_id` (`contact_id`,`usergroup_id`,`date_begin`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `status_category_id` (`status_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Participation Status', NULL, NULL, 'participation-status', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('subscribed', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/subscribed', NULL, 1, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('verified', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/verified', NULL, 2, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('participant', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/participant', NULL, 3, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('missing', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/missing', NULL, 4, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('deleted', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/deleted', NULL, 5, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('blocked', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/blocked', NULL, 6, NOW());

DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
  `activity_id` int unsigned NOT NULL AUTO_INCREMENT,
  `participation_id` int unsigned NOT NULL,
  `activity_category_id` int unsigned NOT NULL,
  `activity_date` datetime NOT NULL,
  `activity_ip` varbinary(16) DEFAULT NULL,
  `activity_uri` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`activity_id`),
  UNIQUE KEY `participation_id_activity_category_id_activity_date` (`participation_id`,`activity_category_id`,`activity_date`),
  KEY `activity_category_id` (`activity_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("Activities", NULL, NULL, "activities", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("subscribe", NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), "activities/subscribe", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("verify", NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), "activities/verify", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("unsubscribe", NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), "activities/unsubscribe", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("plan", NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), "activities/plan", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("send", NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), "activities/send", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("click", NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), "activities/click", NULL, NULL, NOW());
