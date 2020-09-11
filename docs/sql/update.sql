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
