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

DROP TABLE IF EXISTS `participations`;
CREATE TABLE `participations` (
  `participation_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `usergroup_id` int unsigned NOT NULL,
  `date_begin` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `status_category_id` int unsigned NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `last_update` timestamp NOT NULL,
  PRIMARY KEY (`participation_id`),
  KEY `contact_id` (`contact_id`,`usergroup_id`,`date_begin`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `status_category_id` (`status_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("Participation Status", NULL, NULL, "participation-status", NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("subscribed", NULL, (SELECT category_id FROM categories WHERE identifier = "participation-status"), "participation-status/subscribed", NULL, 1, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("verified", NULL, (SELECT category_id FROM categories WHERE identifier = "participation-status"), "participation-status/verified", NULL, 2, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("participant", NULL, (SELECT category_id FROM categories WHERE identifier = "participation-status"), "participation-status/participant", NULL, 3, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("missing", NULL, (SELECT category_id FROM categories WHERE identifier = "participation-status"), "participation-status/missing", NULL, 4, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("deleted", NULL, (SELECT category_id FROM categories WHERE identifier = "participation-status"), "participation-status/deleted", NULL, 5, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ("blocked", NULL, (SELECT category_id FROM categories WHERE identifier = "participation-status"), "participation-status/blocked", NULL, 6, NOW());
