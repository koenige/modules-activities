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
 * @param array $values
 *		string 'identifier'
 *		string 'category_parameters'
 * @return string
 */
function mf_activities_group_path($values) {
	global $zz_setting;
	
	if (!empty($values['category_parameters'])) {
		parse_str($values['category_parameters'], $parameters);
		if (!empty($parameters['no_participations'])) return false;
	}
	
	if (empty($zz_setting['activities_profile_path']['usergroup'])) {
		$success = wrap_setting_path('activities_profile_path[usergroup]', 'forms participations-usergroups');
		if (!$success) return false;
	}
	return sprintf($zz_setting['activities_profile_path']['usergroup'], $values['identifier']);
}

/**
 * get path to group for a contact profile
 *
 * @param array $values
 *		string 'identifier'
 * @return string
 */
function mf_activities_contact_path($values) {
	global $zz_setting;
	
	if (empty($zz_setting['activities_profile_path']['contact'])) {
		$success = wrap_setting_path('activities_profile_path[contact]', 'forms participations-contacts');
		if (!$success) return false;
	}
	return sprintf($zz_setting['activities_profile_path']['contact'], $values['identifier']);
}

/**
 * check for access rights for contact data, mark content
 * with publish=1 or publish=0
 *
 * @param array $contact
 * @param string $access
 */
function mf_activities_contact_access($contact, $access) {
	static $access_properties;
	if (empty($access_properties)) {
		$sql = 'SELECT categories.category_id, categories.category
				, categories.parameters, categories.path
				, main_categories.parameters AS main_parameters
				, main_categories.path AS main_path
			FROM categories
			LEFT JOIN categories main_categories
				ON categories.main_category_id = main_categories.category_id
			WHERE main_categories.parameters LIKE "%&access_property=1%"';
		$access_properties = wrap_db_fetch($sql, 'category_id');
	}
	
	$sql = 'SELECT properties.category_id
			, access.parameters AS parameters
		FROM contacts_access
		LEFT JOIN usergroups USING (usergroup_id)
		LEFT JOIN categories access
			ON access.category_id = contacts_access.access_category_id
		LEFT JOIN categories properties
			ON properties.category_id = contacts_access.property_category_id
		WHERE contact_id = %d
		AND (usergroups.identifier = "%s" OR usergroups.parameters LIKE "%%&alias=%s%%")';
	$sql = sprintf($sql
		, $contact['contact_id']
		, wrap_db_escape($access)
		, wrap_db_escape($access)
	);
	$access_data = wrap_db_fetch($sql, 'category_id');
	
	foreach ($contact as $key => $value) {
		if (!is_array($value)) continue;
		foreach ($value as $detail_key => $detail_value) {
			if (!array_key_exists('category_id', $detail_value)) continue;
			if (!array_key_exists($detail_value['category_id'], $access_properties)) continue;
			if (!array_key_exists($detail_value['category_id'], $access_data)) {
				$contact[$key][$detail_key][$access.'_access'] = false;
			} else {
				parse_str($access_data[$detail_value['category_id']]['parameters'], $parameters);
				$contact[$key][$detail_key][$access.'_access'] = true;
				foreach ($parameters as $pkey => $pvalue) {
					if ($pkey === 'contacts_access') continue;
					$contact[$key][$detail_key][$access.'_'.$pkey] = $pvalue;
				}
			}
		}
	}
	return $contact;
}
