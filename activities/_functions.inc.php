<?php 

/**
 * Zugzwang Project
 * Common functions for activities module
 *
 * http://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_activities_random_hash($fields) {
	if (!empty($fields['verification_hash'])) return $fields['verification_hash'];
	$duplicate = true;
	while ($duplicate) {
		$hash = wrap_random_hash(8);
		$sql = 'SELECT participation_id
			FROM /*_PREFIX_*/participations
			WHERE verification_hash = "%s"';
		$sql = sprintf($sql, $hash);
		$duplicate = wrap_db_fetch($sql, '', 'single value');
	}
	return $hash;
}

/**
 * get path to profile for a group
 *
 * @param string $identifier
 * @return string
 */
function mf_activities_group_path($identifier) {
	global $zz_setting;
	if (empty($zz_setting['activities_profile_path']['usergroup'])) {
		$sql = 'SELECT CONCAT(identifier, IF(ending = "none", "", ending)) AS path
			FROM webpages
			WHERE content LIKE "%%%% forms participations-usergroups * %%%%"';
		$path = wrap_db_fetch($sql, '', 'single value');
		$path = str_replace('*', '/%s', $path);
		if (!$path) return false;
		wrap_setting_write('activities_profile_path[usergroup]', $path);
	}
	return sprintf($zz_setting['activities_profile_path']['usergroup'], $identifier);
}
