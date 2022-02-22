<?php 

/**
 * activities module
 * common functions for activities module
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2022 Gustaf Mossakowski
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

function mf_activities_random_hash_usergroups($fields) {
	if (!empty($fields['registration_hash'])) return $fields['registration_hash'];
	$duplicate = true;
	while ($duplicate) {
		$hash = wrap_random_hash(6);
		$sql = 'SELECT registration_id
			FROM /*_PREFIX_*/registrations
			WHERE registration_hash = "%s"';
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
	return wrap_path('activities_profile[usergroup]', $values['identifier']);
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

	if (!empty($values['category_parameters'])) {
		parse_str($values['category_parameters'], $parameters);
	}
	$type = !empty($parameters['type']) ? $parameters['type'] : 'contact';
	
	// @todo use wrap_path()
	if (empty($zz_setting['activities_profile_path'][$type])) {
		$success = wrap_setting_path('activities_profile_path['.$type.']', 'forms participations-contacts', ['scope' => $type]);
		if (!$success) return false;
	}
	return sprintf($zz_setting['base'].$zz_setting['activities_profile_path'][$type], $values['identifier']);
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
			if (!is_array($detail_value)) continue;
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

/**
 * XHR function that returns keys for access table
 *
 * @param array $cfg
 * @return array
 */
function mf_activities_access_cfg($cfg) {
	if (!array_key_exists('description', $cfg)) $cfg['description'] = '';
	if (!array_key_exists('module', $cfg)) $cfg['module'] = '';
	return [
		$cfg['description'], $cfg['module']
	];
}

/**
 * merge contact with contact with identical name + mail
 *
 * @param int $contact_id
 * @return int old contact_id
 */
function mf_activities_merge_contact($contact_id) {
	$sql = 'SELECT contacts.contact_id, contact, identification
	    FROM contacts
	    LEFT JOIN contactdetails
	    	ON contactdetails.contact_id = contacts.contact_id
	    	AND provider_category_id = %d
	    WHERE contacts.contact_id = %d';
	$sql = sprintf($sql
		, wrap_category_id('provider/e-mail')
		, $contact_id
	);
	$new_contact = wrap_db_fetch($sql);
	
	$sql = 'SELECT contacts.contact_id
		FROM contacts
	    LEFT JOIN contactdetails
	    	ON contactdetails.contact_id = contacts.contact_id
	    	AND provider_category_id = %d
		WHERE contact = "%s" AND contacts.contact_id != %d AND identification = "%s"';
	$sql = sprintf($sql
		, wrap_category_id('provider/e-mail')
		, $new_contact['contact']
		, $contact_id
		, $new_contact['identification']
	);
	$old_contact_id = wrap_db_fetch($sql, '', 'single value');
	if (!$old_contact_id) return false;
	
	$changes = [
		'update' => [
			'address_id' => 'addresses', 'participation_id' => 'participations'
		],
		'delete' => [
			'contact_id' => 'contacts', 'contactdetail_id' => 'contactdetails'
		]
	];
	$sql = 'SELECT %s FROM %s WHERE contact_id = %d';
	foreach ($changes as $action => $update) {
		foreach ($update as $field_name => $table) {
			$this_sql = sprintf($sql, $field_name, $table, $contact_id);
			$ids = wrap_db_fetch($this_sql, $field_name, 'single value');
			foreach ($ids as $id) {
				$values = [];
				$values['action'] = $action;
				$values['ids'][] = $field_name;
				$values['POST'][$field_name] = $id;
				if ($action === 'update') {
					$values['ids'][] = 'contact_id';
					$values['POST']['contact_id'] = $old_contact_id;
				}
				$ops = zzform_multi($table, $values);
				if (!$ops['id']) {
					wrap_error(sprintf('Merging contacts was not successful. Failed to %s ID %d (table %s)'
						, $action, $id, $table
					));
					return false;
				}
			}
		}
	}
	return $old_contact_id;
}
