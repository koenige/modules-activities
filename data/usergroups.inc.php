<?php 

/**
 * activities module
 * get usergroup data per ID
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get usergroup data per ID, pre-sorted
 * existing data is appended to usergroup data
 *
 * @param array $ids
 * @param array $langs
 * @param array $settings (optional)
 * @return array
 */
function mf_activities_usergroups_data($ids, $langs, $settings = []) {
	$sql = 'SELECT usergroup_id, usergroup, identifier, usergroups.description
			, categories.parameters AS category_parameters
			, usergroups.parameters AS usergroup_parameters
		FROM usergroups
		LEFT JOIN categories
			ON usergroups.usergroup_category_id = categories.category_id
		WHERE usergroup_id IN (%s)';
	$sql = sprintf($sql, implode(',', $ids));
	$data = wrap_db_fetch($sql, 'usergroup_id');
	foreach ($data as $usergroup_id => $usergroup) {
		if ($usergroup['category_parameters'])
			parse_str($usergroup['category_parameters'], $usergroup['c']);
		if ($usergroup['usergroup_parameters'])
			parse_str($usergroup['usergroup_parameters'], $usergroup['u']);
		$data[$usergroup_id]['parameters'] = wrap_array_merge(
			$usergroup['c'] ?? [], $usergroup['u'] ?? []
		);
		unset($data[$usergroup_id]['category_parameters']);
		unset($data[$usergroup_id]['usergroup_parameters']);
	}

	$usergroups = [];
	foreach ($langs as $lang) {
		$usergroups[$lang] = wrap_translate($data, 'usergroups', '', true, $lang);
		foreach (array_keys($usergroups[$lang]) as $usergroup_id) {
			$usergroups[$lang][$usergroup_id][$lang] = true;
		}
	}

	return [$usergroups];
}	
