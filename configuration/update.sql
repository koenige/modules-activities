/**
 * Zugzwang Project
 * SQL updates for activities module
 *
 * http://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/* 2020-09-10-1 */	ALTER TABLE `usergroups` CHANGE `usergroup` `usergroup` varchar(80) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `usergroup_id`;
/* 2020-11-30-2 */	ALTER TABLE `activities` CHANGE `activity_uri` `activity_uri` varchar(255) COLLATE 'latin1_general_ci' NOT NULL AFTER `activity_ip`;
/* 2020-11-30-3 */	ALTER TABLE `activities` ADD UNIQUE `participation_id_activity_category_id_activity_date_activity_uri` (`participation_id`, `activity_category_id`, `activity_date`, `activity_uri`), DROP INDEX `participation_id_activity_category_id_activity_date`;
/* 2021-02-23-1 */	CREATE TABLE `contacts_access` (`contact_access_id` int unsigned NOT NULL AUTO_INCREMENT, `contact_id` int unsigned DEFAULT NULL, `usergroup_id` int unsigned NOT NULL, `access_category_id` int unsigned NOT NULL, `property_category_id` int unsigned NOT NULL, `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`contact_access_id`), UNIQUE KEY `contact_id_usergroup_id_property_category_id` (`contact_id`,`usergroup_id`,`property_category_id`), KEY `usergroup_id` (`usergroup_id`), KEY `access_category_id` (`access_category_id`), KEY `property_category_id` (`property_category_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2021-02-23-2 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'contacts', 'contact_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'contact_id', 'no-delete');
/* 2021-02-23-3 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'usergroups', 'usergroup_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'usergroup_id', 'no-delete');
/* 2021-02-23-4 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'access_category_id', 'no-delete');
/* 2021-02-23-5 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'contacts_access', 'contact_access_id', 'property_category_id', 'no-delete');
/* 2021-03-14-1 */	ALTER TABLE `participations` ADD `sequence` smallint unsigned NULL AFTER `status_category_id`;
