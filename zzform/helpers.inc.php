<?php 

/**
 * activities module
 * editing functions for zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * list view: HTML for groups via include_access
 *
 * @param string $key access_key
 * @return string
 */
function mf_activities_access_usergroups_included_list($key) {
	if (!$key) return '';
	$data = mf_activities_access_usergroups_included_template($key);
	return wrap_template('access-usergroups-list', $data);
}

/**
 * record view: HTML for groups via include_access
 *
 * @param string $key access_key
 * @return string
 */
function mf_activities_access_usergroups_included_record($key) {
	if (!$key) return '';
	$data = mf_activities_access_usergroups_included_template($key);
	if (!$data) $data['no_includes'] = true;
	return wrap_template('access-usergroups-record', $data);
}

/**
 * template data for included usergroups per access key
 *
 * @param string $key access_key
 * @return array template loop data
 */
function mf_activities_access_usergroups_included_template($key) {
	$included = mf_activities_access_usergroups_included($key);
	$data = [];
	foreach ($included as $access_key => $usergroups) {
		$groups = [];
		foreach ($usergroups as $group)
			$groups[]['usergroup'] = $group;
		$data[] = [
			'access_key' => $access_key,
			'usergroups' => $groups
		];
	}
	return $data;
}
