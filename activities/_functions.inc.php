<?php 

/**
 * activities module
 * common functions for activities module
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2023 Gustaf Mossakowski
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

/**
 * get data from all form fields
 *
 * @param int $contact_id
 * @param int $form_id
 * @return array
 */
function mf_activities_formfielddata($contact_id, $form_id) {
	$sql = 'SELECT formfield_id, formfield, area, categories.parameters
		FROM formfields
		LEFT JOIN categories
			ON formfields.formfield_category_id = categories.category_id
		WHERE form_id = %d
		AND (ISNULL(formfields.parameters)
			OR formfields.parameters NOT LIKE "%%&hide_in_formfielddata=1%%")
		ORDER BY formfields.sequence';
	$sql = sprintf($sql, $form_id);
	$fields = wrap_db_fetch($sql, 'formfield_id');
	$fields = wrap_translate($fields, 'formfields');
	
	$db_fields = [];
	foreach ($fields as &$field) {
		if (empty($field['parameters'])) continue;
		parse_str($field['parameters'], $field['parameters']);
		if (empty($field['parameters']['db_field'])) continue;
		list($table_name, $field_name) = explode('.', $field['parameters']['db_field']);
		$db_fields[$table_name][$field['formfield_id']] = $field_name;
		if (empty($field['parameters']['db_fields'])) continue;
		$extra_fields[$table_name][$field['formfield_id']] = $field['parameters']['db_fields'];
	}
	foreach ($db_fields as $table_name => $table_fields) {
		if (in_array($table_name, ['persons', 'contacts'])) {
			// SELECT first_name, last_name, sex FROM persons WHERE contact_id = 23
			$sql = 'SELECT %s FROM %s WHERE contact_id = %d';
			$sql = sprintf($sql, implode(', ', array_unique($table_fields)), $table_name, $contact_id);
			$record = wrap_db_fetch($sql);
			foreach ($table_fields as $formfield_id => $field_name) {
				$fields[$formfield_id]['value'][] = mf_activities_formfielddata_format($fields[$formfield_id], $record[$field_name]);
			}
		} else {
			// SELECT formfield_id, identification FROM contactdetails WHERE contact_id = 23 AND formfield_id IN (%s)
			switch ($table_name) {
			case 'addresses':
				$join = 'LEFT JOIN countries USING (country_id)'; break;
			case 'media':
				$join = 'LEFT JOIN filetypes USING (filetype_id)'; break;
			default:
				$join = '';
			}
			$sql = 'SELECT formfield_id, %s FROM %s %s WHERE contact_id = %d AND formfield_id IN (%s)';
			$field_names = array_unique($table_fields);
			if (!empty($extra_fields[$table_name])) {
				foreach ($extra_fields[$table_name] as $extra_field_names) {
					$field_names += $extra_field_names;
				}
				$field_names = array_unique($field_names);
			}
			$sql = sprintf($sql
				, implode(', ', $field_names)
				, $table_name
				, $join
				, $contact_id
				, implode(', ', array_keys($table_fields))
			);
			$record = wrap_db_fetch($sql, 'formfield_id');
			foreach ($table_fields as $formfield_id => $field_name) {
				if (empty($record[$formfield_id])) continue; // no value
				$fields[$formfield_id]['value'][] = mf_activities_formfielddata_format($fields[$formfield_id], $record[$formfield_id][$field_name]);
			}
			if (!empty($extra_fields[$table_name])) {
				foreach ($extra_fields[$table_name] as $formfield_id => $field_names) {
					if (empty($record[$formfield_id])) continue; // no value
					foreach ($field_names as $field_name) {
						$field_name = explode('.', $field_name);
						$field_name = $field_name[1];
						$fields[$formfield_id]['value'][] = $record[$formfield_id][$field_name];
					}
				}
			}
		}
	}
	foreach (array_keys($fields) AS $formfield_id) {
		if (empty($fields[$formfield_id]['value']))
			$fields[$formfield_id]['value'] = '';
		else
			$fields[$formfield_id]['value'] = implode('; ', $fields[$formfield_id]['value']);
	}
	return $fields;
}

function mf_activities_formfielddata_format($form_field, $value) {
	if (!empty($form_field['parameters']['format']))
		$value = $form_field['parameters']['format']($value);
	return $value;
}

/**
 * get templates for a form
 *
 * @param int $form_id
 * @return array
 *		authentication = tpl
 *		confirmation = tpl
 *		field-changed[field_id] = tpl
 */
function mf_activities_form_templates($form_id) {
	$sql = 'SELECT formtemplate_id, template, formfield_id, template_category_id
			, categories.parameters
			, SUBSTRING_INDEX(categories.path, "/", -1) AS path_fragment
		FROM formtemplates
		LEFT JOIN categories
			ON formtemplates.template_category_id = categories.category_id
		WHERE form_id = %d';
	$sql = sprintf($sql, $form_id);
	$templates = wrap_db_fetch($sql, 'formtemplate_id');
	$templates = wrap_translate($templates, 'formtemplates');
	
	$data = [];
	foreach ($templates as $template) {
		if ($template['parameters']) parse_str($template['parameters'], $template['parameters']);
		else $template['parameters'] = [];
		$key = !empty($template['parameters']['alias'])
			? substr($template['parameters']['alias'], strrpos($template['parameters']['alias'], '/') + 1)
			: $template['path_fragment'];
		if (!empty($template['parameters']['formfield']))
			$data[$key][$template['formfield_id']] = $template['template'];
		else
			$data[$key] = $template['template'];
	}
	return $data;
}
