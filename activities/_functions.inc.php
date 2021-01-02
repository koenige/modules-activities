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
