/**
 * activities module
 * SQL for installation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


CREATE TABLE `access` (
  `access_id` int unsigned NOT NULL AUTO_INCREMENT,
  `access_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `module` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`access_id`),
  UNIQUE KEY `access_key` (`access_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `access_usergroups` (
  `access_usergroup_id` int unsigned NOT NULL AUTO_INCREMENT,
  `access_id` int unsigned NOT NULL,
  `usergroup_id` int unsigned NOT NULL,
  `restricted_to_field` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`access_usergroup_id`),
  UNIQUE KEY `access_id_usergroup_id` (`access_id`,`usergroup_id`),
  KEY `usergroup_id` (`usergroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'access', 'access_id', (SELECT DATABASE()), 'access_usergroups', 'access_usergroup_id', 'access_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'usergroups', 'usergroup_id', (SELECT DATABASE()), 'access_usergroups', 'access_usergroup_id', 'usergroup_id', 'no-delete');


CREATE TABLE `contacts_access` (
  `contact_access_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned DEFAULT NULL,
  `usergroup_id` int unsigned NOT NULL,
  `access_category_id` int unsigned NOT NULL,
  `property_category_id` int unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_access_id`),
  UNIQUE KEY `contact_id_usergroup_id_property_category_id` (`contact_id`,`usergroup_id`,`property_category_id`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `access_category_id` (`access_category_id`),
  KEY `property_category_id` (`property_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'usergroups', 'usergroup_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'usergroup_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'access_category_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'property_category_id', 'no-delete');


CREATE TABLE `forms` (
  `form_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned DEFAULT NULL,
  `access` enum('public','login') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `created` datetime NOT NULL,
  `header` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `lead` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `footer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `authentication_mail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `confirmation_mail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `form_category_id` int unsigned DEFAULT NULL,
  `address` enum('formal','informal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'formal',
  PRIMARY KEY (`form_id`),
  UNIQUE KEY `event_id` (`event_id`),
  KEY `form_category_id` (`form_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'forms', 'form_id', 'event_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'forms', 'form_id', 'form_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Forms', NULL, NULL, 'forms', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Registration', NULL, (SELECT category_id FROM categories c WHERE path = 'forms'), 'forms/registration', NULL, 1, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Application', NULL, (SELECT category_id FROM categories c WHERE path = 'forms'), 'forms/application', NULL, 1, NOW());


CREATE TABLE `formfields` (
  `formfield_id` int unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int unsigned NOT NULL,
  `formfield` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` varchar(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formfield_category_id` int unsigned NOT NULL,
  `area` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sequence` tinyint unsigned NOT NULL,
  `parameters` varchar(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edit_from` datetime DEFAULT NULL,
  `edit_by` datetime DEFAULT NULL,
  `main_formfield_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`formfield_id`),
  KEY `form_id` (`form_id`),
  KEY `formfield_category_id` (`formfield_category_id`),
  KEY `main_formfield_id` (`main_formfield_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'forms', 'form_id', (SELECT DATABASE()), 'formfields', 'formfield_id', 'form_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'formfields', 'formfield_id', 'formfield_category_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'formfields', 'formfield_id', (SELECT DATABASE()), 'formfields', 'formfield_id', 'main_formfield_id', 'delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Field Types', NULL, NULL, 'field-types', NULL, NULL, NOW());


CREATE TABLE `registrations` (
  `registration_id` int unsigned NOT NULL AUTO_INCREMENT,
  `usergroup_id` int unsigned NOT NULL,
  `event_id` int unsigned DEFAULT NULL,
  `organisation_contact_id` int unsigned DEFAULT NULL,
  `min_participants` smallint unsigned DEFAULT NULL,
  `max_participants` smallint unsigned DEFAULT NULL,
  `waiting_participants` smallint unsigned DEFAULT NULL,
  `show_remaining_from` smallint unsigned DEFAULT NULL,
  `registration_hash` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `usergroup_id` (`usergroup_id`,`event_id`),
  KEY `event_id` (`event_id`),
  KEY `contact_id` (`organisation_contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'usergroups', 'usergroup_id', (SELECT DATABASE()), 'registrations', 'registration_id', 'usergroup_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'registrations', 'registration_id', 'event_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'registrations', 'registration_id', 'organisation_contact_id', 'no-delete');


CREATE TABLE `registrationtexts` (
  `registrationtext_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `formfield_id` int unsigned NOT NULL,
  `registrationtext` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`registrationtext_id`),
  UNIQUE KEY `contact_id` (`contact_id`,`formfield_id`),
  KEY `formfield_id` (`formfield_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'registrationtexts', 'registrationtext_id', 'contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'formfields', 'formfield_id', (SELECT DATABASE()), 'registrationtexts', 'registrationtext_id', 'formfield_id', 'no-delete');


CREATE TABLE `registrationvarchars` (
  `registrationvarchar_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `formfield_id` int unsigned NOT NULL,
  `registrationvarchar` varchar(750) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`registrationvarchar_id`),
  UNIQUE KEY `contact_id` (`contact_id`,`formfield_id`),
  KEY `formfield_id` (`formfield_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'registrationvarchars', 'registrationvarchar_id', 'contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'formfields', 'formfield_id', (SELECT DATABASE()), 'registrationvarchars', 'registrationvarchar_id', 'formfield_id', 'no-delete');


CREATE TABLE `usergroups` (
  `usergroup_id` int unsigned NOT NULL AUTO_INCREMENT,
  `usergroup` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
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

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'usergroups', 'usergroup_id', 'usergroup_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Usergroups', NULL, NULL, 'usergroups', NULL, NULL, NOW());


CREATE TABLE `participations` (
  `participation_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int unsigned NOT NULL,
  `usergroup_id` int unsigned NOT NULL,
  `event_id` int unsigned DEFAULT NULL,
  `date_begin` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `status_category_id` int unsigned NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sequence` smallint unsigned DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `verification_hash` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` timestamp NOT NULL,
  PRIMARY KEY (`participation_id`),
  UNIQUE KEY `verification_hash` (`verification_hash`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `status_category_id` (`status_category_id`),
  KEY `contact_id_usergroup_id_date_begin_event_id` (`contact_id`,`usergroup_id`,`date_begin`,`event_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'participations', 'participation_id', 'contact_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'participations', 'participation_id', 'status_category_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'usergroups', 'usergroup_id', (SELECT DATABASE()), 'participations', 'participation_id', 'usergroup_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'participations', 'participation_id', 'event_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Participation Status', NULL, NULL, 'participation-status', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('subscribed', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/subscribed', NULL, 1, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('verified', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/verified', NULL, 2, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('participant', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/participant', NULL, 3, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('missing', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/missing', NULL, 4, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('deleted', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/deleted', NULL, 5, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('blocked', NULL, (SELECT category_id FROM categories c WHERE path = 'participation-status'), 'participation-status/blocked', NULL, 6, NOW());


CREATE TABLE `activities` (
  `activity_id` int unsigned NOT NULL AUTO_INCREMENT,
  `participation_id` int unsigned NOT NULL,
  `activity_category_id` int unsigned NOT NULL,
  `activity_date` datetime NOT NULL,
  `activity_ip` varbinary(16) DEFAULT NULL,
  `activity_uri` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`activity_id`),
  UNIQUE KEY `participation_id_activity_category_id_activity_date_activity_uri` (`participation_id`,`activity_category_id`,`activity_date`,`activity_uri`),
  KEY `activity_category_id` (`activity_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'participations', 'participation_id', (SELECT DATABASE()), 'activities', 'activity_id', 'participation_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'activities', 'activity_id', 'activity_category_id', 'no-delete');

INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Activities', NULL, NULL, 'activities', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('subscribe', NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), 'activities/subscribe', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('verify', NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), 'activities/verify', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('unsubscribe', NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), 'activities/unsubscribe', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('mail', NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), 'activities/mail', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('send', NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), 'activities/send', NULL, NULL, NOW());
INSERT INTO categories (`category`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('click', NULL, (SELECT category_id FROM categories c WHERE path = 'activities'), 'activities/click', NULL, NULL, NOW());
