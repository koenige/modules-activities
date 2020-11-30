/**
 * Zugzwang Project
 * SQL updates for registrations module
 *
 * http://www.zugzwang.org/modules/registrations
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/* 2020-09-10-1 */	ALTER TABLE `usergroups` CHANGE `usergroup` `usergroup` varchar(80) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `usergroup_id`;
/* 2020-11-30-1 */	ALTER TABLE `activities` CHANGE `activity_uri` `activity_uri` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `activity_ip`;
/* 2020-11-30-2 */	ALTER TABLE `activities` CHANGE `activity_uri` `activity_uri` varchar(255) COLLATE 'latin1_general_ci' NOT NULL AFTER `activity_ip`;
/* 2020-11-30-3 */	ALTER TABLE `activities` ADD UNIQUE `participation_id_activity_category_id_activity_date_activity_uri` (`participation_id`, `activity_category_id`, `activity_date`, `activity_uri`), DROP INDEX `participation_id_activity_category_id_activity_date`;
