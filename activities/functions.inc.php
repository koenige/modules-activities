<?php 

/**
 * activities module
 * common functions for activities module
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get path to profile for a group
 *
 * @param array $values
 *		string 'identifier'
 *		string 'category_parameters'
 * @return string
 */
function mf_activities_group_path($values) {
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
	if (!empty($values['category_parameters'])) {
		parse_str($values['category_parameters'], $parameters);
	}
	$type = !empty($parameters['type']) ? $parameters['type'] : 'contact';
	
	// @todo use wrap_path()
	if (!wrap_setting('activities_profile_path['.$type.']')) {
		$success = wrap_setting_path('activities_profile_path['.$type.']', 'forms participations-contacts', ['scope' => $type]);
		if (!$success) return false;
	}
	return sprintf(wrap_setting('base').wrap_setting('activities_profile_path['.$type.']'), $values['identifier']);
}

/**
 * check for access rights for contact data, mark content
 * with publish=1 or publish=0
 *
 * @param array $contact
 * @param string $access
 */
function mf_activities_contact_access($contact, $access) {
	static $access_properties = [];
	if (!$access_properties) {
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
	if (!array_key_exists('package', $cfg)) $cfg['package'] = '';
	return [
		$cfg['description'], $cfg['package']
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
			switch ($action) {
			case 'delete':
				$success = zzform_delete($table, $ids, E_USER_NOTICE, ['msg' => 'Merging contacts was not successful.']);
				if (count($success) !== count($ids)) return false;
				break;
			case 'update':
				foreach ($ids as $id) {
					$line = [
						$field_name => $id,
						'contact_id' => $old_contact_id
					];
					$success = zzform_update($table, $line, E_USER_NOTICE, ['msg' => 'Merging contacts was not successful.']);
					if (!$sucess) return false;
				}
				break;
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
	$sql = 'SELECT formfield_id, formfield, area, categories.parameters, main_formfield_id
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
	
	$defs = mf_activities_formfielddata_defs($fields);
	$fields = mf_activities_formfielddata_values($contact_id, $fields, $defs);
	foreach ($fields as $formfield_id => $field) {
		if ($field['main_formfield_id'] === 'NULL') continue;
		if (!array_key_exists($field['main_formfield_id'], $fields)) {
			unset($fields[$formfield_id]);
			continue; // copy error
		}
		$fields[$field['main_formfield_id']]['formfield_titles'][] = $field['formfield'];
		unset($fields[$formfield_id]);
	}
	foreach (array_keys($fields) AS $formfield_id) {
		unset($fields[$formfield_id]['main_formfield_id']);
		$fields[$formfield_id]['values'] = mf_activities_formfielddata_format($fields[$formfield_id], $fields[$formfield_id]['values'] ?? []);
		$fields[$formfield_id]['value'] = mf_activities_formfielddata_concat($fields[$formfield_id], $fields[$formfield_id]['values']);
		$fields[$formfield_id]['formfield_title'] = mf_activities_formfielddata_concat($fields[$formfield_id], $fields[$formfield_id]['formfield_titles'] ?? []);
	}
	return $fields;
}

/**
 * sort formfield_ids per main_formfield_id, table in key _fields
 * get table definitions, sorted per table in key with table name
 *
 * @param array $fields
 * @return array
 */
function mf_activities_formfielddata_defs(&$fields) {
	$keys = ['db_foreign_key', 'db_fields', 'db_joins', 'db_where_field'];
	$defs = [];
	foreach ($fields as &$field) {
		if (empty($field['parameters'])) continue;
		parse_str($field['parameters'], $field['parameters']);
		if (empty($field['parameters']['db_field'])) continue;
		if (!$field['main_formfield_id']) $field['main_formfield_id'] = 'NULL';
		list($table_name, $field_name) = explode('.', $field['parameters']['db_field']);
		$defs['_fields'][$field['main_formfield_id']][$table_name][$field['formfield_id']] = $field_name;
		foreach ($keys as $key) {
			if (empty($field['parameters'][$key])) continue;
			if (str_ends_with($key, 's')) {
				if (empty($defs[$table_name][$key])) $defs[$table_name][$key] = [];
				$defs[$table_name][$key] += $field['parameters'][$key];
			} else {
				$defs[$table_name][$key] = $field['parameters'][$key];
			}
		}
	}
	return $defs;
}

/**
 * write all values into form fields
 *
 * @param int $contact_id
 * @param array $fields
 * @param array $defs
 * @param string $top_key, optional
 * @return array
 */
function mf_activities_formfielddata_values($contact_id, $fields, $defs, $top_key = 'NULL') {
	foreach ($defs['_fields'][$top_key] as $table_name => $table_fields) {
		// get query
		$def = $defs[$table_name] ?? [];
		if ($fk = $def['db_foreign_key'] ?? '')
			$sql = sprintf('SELECT %s, %%s FROM %%s %%s WHERE %%s = %%d AND %s IN (%%s)', $fk, $fk);
		else
			$sql = 'SELECT %s FROM %s %s WHERE %s = %d %s';
		$field_names = array_unique($table_fields);
		if (!empty($def['db_fields'])) {
			$field_names += $def['db_fields'];
			$field_names = array_unique($field_names);
		}
		
		$joins = mf_formkit_joins($def['db_joins'] ?? []);

		// get data from database
		$sql = sprintf($sql
			, implode(', ', $field_names)
			, $table_name
			, implode(' ', $joins)
			, $def['db_where_field'] ?? 'contact_id'
			, $contact_id
			, $fk ? implode(', ', array_keys($table_fields)) : ''
		);
		if ($fk) {
			$record = wrap_db_fetch($sql, $fk, 'numeric');
			$record = mf_activities_formfielddata_record($record);
		} else {
			$record = wrap_db_fetch($sql);
		}

		// format values
		foreach ($table_fields as $formfield_id => $field_name) {
			if ($fk)
				$value = $record[$formfield_id][$field_name] ?? '';
			else
				$value = $record[$field_name] ?? '';
			if (!$value) continue;
			if (!empty($defs['_fields'][$formfield_id])) {
				if (!is_array($value)) $value = [$value];
				$details = [];
				foreach ($value as $id) {
					$data = mf_activities_formfielddata_values($id, $fields, $defs, $formfield_id);
					foreach ($defs['_fields'][$formfield_id] as $fielddef) {
						foreach ($fielddef as $my_formfield_id => $my_table) {
							if (!array_key_exists($id, $details)) $details[$id] = [];
							$details[$id] = array_merge($details[$id], $data[$my_formfield_id]['values'] ?? [0 => '']);
						}
					}
				}
				$fields[$formfield_id]['values'] = $details;
			} else {
				$fields[$formfield_id]['values'][] = $value;
			}
			if (!empty($def['db_fields'])) foreach ($def['db_fields'] as $field_name) {
				$field_name = explode('.', $field_name);
				$field_name = $field_name[1];
				$fields[$formfield_id]['values'][] = $record[$formfield_id][$field_name];
			}
		}
	}
	return $fields;
}

/**
 * create JOINs from definition
 *
 * @param array $db_joins
 * @return array
 */
function mf_formkit_joins($db_joins) {
	$joins = [];
	if (!$db_joins) return $joins;
	foreach ($db_joins as $db_join) {
		$db_join = explode('.', $db_join);
		$joins[] = vsprintf('LEFT JOIN %s USING (%s)', $db_join);
	}
	return $joins;
}

/**
 * index record by formfield_id
 *
 * @param array $record
 * @return array
 */
function mf_activities_formfielddata_record($record) {
	$new = [];
	$details = [];
	foreach ($record as $line) {
		if (array_key_exists($line['formfield_id'], $new)) {
			if (!is_numeric(key($new[$line['formfield_id']])))
				$new[$line['formfield_id']] = [$new[$line['formfield_id']]];
			$new[$line['formfield_id']][] = $line;
			$details[] = $line['formfield_id'];
		} else {
			$new[$line['formfield_id']] = $line;
		}
	}
	foreach ($details as $formfield_id) {
		foreach ($new[$formfield_id] as $line)
			foreach ($line as $field => $value)
				$new[$formfield_id][$field][] = $value;
	}
	return $new;
}

/**
 * format value in form field
 *
 * @param array $form_field
 * @param mixed $value
 * @return array
 */
function mf_activities_formfielddata_format($form_field, $values) {
	if (empty($form_field['parameters']['format'])) return $values;
	$first = reset($values);
	if (is_array($first)) {
		$result = [];
		foreach ($values as $index => $line)
			$result[] = mf_activities_formfielddata_format($form_field, $line);
		return $result;
	}
	foreach ($form_field['parameters']['format'] as $index => $format) {
		if (empty($values[$index])) continue;
		$values[$index] = $format($values[$index]);
	}
	return $values;
}

/**
 * concat values/titles in form field
 *
 * @param array $form_field
 * @param mixed $value
 * @return string
 */
function mf_activities_formfielddata_concat($form_field, $values) {
	if (!$values) return '';
	$first = reset($values);
	if (is_array($first)) {
		$result = [];
		foreach ($values as $index => $line)
			$result[] = mf_activities_formfielddata_concat($form_field, $line);
		return implode(wrap_setting('activities_formfielddata_concat_rows'), $result);
	}
	if (empty($form_field['parameters']['concat']))
		return implode(wrap_setting('activities_formfielddata_concat_fields'), $values);
	$result = '';
	foreach ($values as $index => $value) {
		$result .= $value;
		if (array_key_exists($index, $form_field['parameters']['concat'])) {
			$form_field['parameters']['concat'][$index] = trim($form_field['parameters']['concat'][$index], '"');
			$result .= $form_field['parameters']['concat'][$index];
		} elseif ($index !== count($values) - 1) {
			$result .= wrap_setting('activities_formfielddata_concat_fields');
		}
	}
	return $result;
}

/**
 * get list of required fields per form
 *
 * @param array $data
 * @return array
 */
function mf_activities_formfields_required($data) {
	$sql = 'SELECT categories.parameters
		FROM forms
		LEFT JOIN categories
			ON forms.form_category_id = categories.category_id
		WHERE form_id = %d';
	$sql = sprintf($sql, $data['form_id']);
	$parameters = wrap_db_fetch($sql, '', 'single value');
	if ($parameters) parse_str($parameters, $parameters);

	if (!empty($data['website_id'])) {
		$sql = 'SELECT contacts.parameters
			FROM websites
			LEFT JOIN contacts
				ON websites.contact_id = contacts.contact_id
			WHERE website_id = %d';
		$sql = sprintf($sql, $data['website_id']);
		$parameters_org = wrap_db_fetch($sql, '', 'single value');
		if ($parameters_org) {
			parse_str($parameters_org, $parameters_org);
			$parameters = array_merge($parameters, $parameters_org);
		}
	}
	if (!$parameters) return [];
	if (!array_key_exists('required_fields', $parameters)) return [];
	
	$category_ids = [];
	foreach ($parameters['required_fields'] as $field)
		if (!wrap_category_id('field-types/'.$field, 'check'))
			wrap_error(sprintf('Configuration error. Field type %s does not exist.', $field), E_USER_ERROR);
		else
			$category_ids[] = wrap_category_id('field-types/'.$field);

	$sql = 'SELECT category_id, category
		FROM categories
		WHERE category_id IN (%s)
		ORDER BY FIELD(category_id, %s)';
	$sql = sprintf($sql
		, implode(',', $category_ids)
		, implode(',', $category_ids)
	);
	$categories = wrap_db_fetch($sql, 'category_id');
	$categories = wrap_translate($categories, 'categories');
	$categories['text'] = [];
	foreach ($categories as $key => $category)
		if (!is_numeric($key)) continue;
		else $categories['text'][] = $category['category'];

	if (!empty($data['formfield_category_ids']))
		$categories['missing'] = array_diff($category_ids, $data['formfield_category_ids']);

	return $categories;
}

/**
 * get templates for a form
 *
 * @param int $form_id
 * @param string $type (optional)
 * @param int $formfield_id (optional)
 * @return array
 *		authentication = tpl
 *		confirmation = tpl
 *		field-changed[field_id] = tpl
 */
function mf_activities_form_templates($form_id, $type = '', $formfield_id = 0) {
	static $data = [];
	
	if (!$data) {
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
	}
	if ($type and $formfield_id) return $data[$type][$formfield_id] ?? '';
	if ($type) return $data[$type];
	return $data;
}

/**
 * get form with event data
 * plus checks
 *
 * @param string $identifier
 * @param int $event_category_id
 * @param int $website_id (optional)
 * @return array
 */
function mf_activities_form($identifier, $event_category_id = NULL, $website_id = NULL) {
	if (!$website_id) $website_id = wrap_setting('website_id') ?? 1;
	if (!$event_category_id) $event_category_id = wrap_category_id('event/event');
	$sql = wrap_sql_query('activities_placeholder_form');
	$sql = sprintf($sql
		, wrap_db_escape($identifier)
		, $event_category_id
		, $website_id
	);
	$event = wrap_db_fetch($sql);
	if (!$event) return [];
	$event = wrap_translate($event, 'event');
	$event = wrap_translate($event, 'categories', 'category_id');
	$event = wrap_translate($event, 'forms', 'form_id');
	if (!$event['formtemplates_confirmation_missing'] AND !$event['formtemplates_authentication_missing'])
		$event['formtemplates'] = true;
	if ($event['formfield_category_ids'])
		$event['formfield_category_ids'] = explode(',', $event['formfield_category_ids']);
	if ($event['form_parameters'])
		parse_str($event['form_parameters'], $event['form_parameters']);

	$required = mf_activities_formfields_required($event);
	if (empty($required['missing'])) $event['formfields'] = true;
	return $event;
}

/**
 * check if an event has a form and return it
 *
 * @param array $event
 * @return string
 */
function mf_activities_event_form($event) {
	$sql = 'SELECT form_id FROM forms WHERE event_id = %d';
	$sql = sprintf($sql, $event['event_id']);
	$form = wrap_db_fetch($sql, '', 'single value');
	if (!$form) return '';
	$event = array_merge($event, mf_activities_form($event['identifier'], wrap_category_id('event/event')));
	$form = brick_format('%%% forms registration '.$event['identifier'].' %%%', $event);
	return $form['text'];
}
