<?php

/**
 * activities module
 * usergroups functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get one usergroup by identifier
 *
 * @param string $identifier
 * @return array|false
 */
function mf_activities_usergroup($identifier) {
	$usergroup_id = wrap_id('usergroups', $identifier);
	if (!$usergroup_id) return false;

	wrap_include('data', 'zzwrap');
	$usergroups = wrap_data('usergroups',
		[$usergroup_id => ['usergroup_id' => $usergroup_id]]
	);
	$data = $usergroups[$usergroup_id] ?? [];
	if (!$data) return false;
	
	return $data;
}
