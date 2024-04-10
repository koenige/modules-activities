<?php 

/**
 * activities module
 * form kit
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * create a form based on formfields
 *
 * @param array $zz
 * @param int $event_id
 * @param array $parameters
 * @return array
 */
function mf_activities_formkit($zz, $event_id, $parameters) {
	$sql = 'SELECT formfield_id, formfield, explanation, area
			, category_id, categories.path
			, edit_from, edit_by
			, formfields.parameters AS custom
			, categories.parameters AS definition
		FROM formfields
		LEFT JOIN forms USING (form_id)
		LEFT JOIN events USING (event_id)
		LEFT JOIN categories
			ON formfields.formfield_category_id = categories.category_id
		WHERE event_id = %d
		AND ISNULL(main_formfield_id)
		ORDER BY formfields.sequence';
	$sql = sprintf($sql, $event_id);
	$formfields = wrap_db_fetch($sql, 'formfield_id');
	$formfields = wrap_translate($formfields, 'formfields');

	foreach ($formfields as $formfield_id => $formfield) {
		if ($formfield['custom'])
			parse_str($formfield['custom'], $formfields[$formfield_id]['custom']);
		if ($formfield['definition'])
			parse_str($formfield['definition'], $formfields[$formfield_id]['definition']);
	}

	mf_activities_formkit_contacts($zz, $parameters);
	$last_update = $zz['fields'][99];
	unset($zz['fields'][99]);
	$no = mf_activities_formkit_no($zz['fields']);
	
	$area = '';
	foreach ($formfields as $formfield) {
		$my_no = $no;
		if (empty($formfield['definition']['db_field'])) continue; // @todo, captcha
		// @todo show_without_login=0 and form = login: continue;
		// if (form === login AND empty($formfield['definition']['show_without_login'])) continue;
		$formfield['custom'] = mf_activities_formkit_normalize_parameters($formfield['custom']);
		list($formfield['table'], $formfield['field_name']) = explode('.', $formfield['definition']['db_field']);
		if ($formfield['table'] === $zz['table']) {
			$my_no = mf_activities_formkit_field($formfield, $zz['fields']);
			$zz['fields'][$my_no]['type'] = $formfield['definition']['type'];
		} else {
			$zz['fields'][$my_no] = mf_activities_formkit_subtable($formfield, $my_no);
		}
		$zz['fields'][$my_no]['title'] = $formfield['formfield'];
		$zz['fields'][$my_no]['explanation'] = $formfield['explanation'];
		$zz['fields'][$my_no]['hide_in_form'] = false;
		$zz['fields'][$my_no]['export'] = true;
		$zz['fields'][$my_no]['hide_in_list'] = $formfield['custom']['hide_in_list'] ?? false;
		if ($formfield['area'] AND $formfield['area'] !== $area) {
			$zz['fields'][$my_no]['separator_before'] = 'text <h3><strong>'.$formfield['area'].'</strong></h3>';
			$area = $formfield['area'];
		}
		$no++;
	}
	
	$last_update['hide_in_form'] = true;
	$zz['fields'][] = $last_update;
	return $zz;
}

/**
 * prepare main table (contacts)
 * hide fields from list, record, export, set values
 *
 * @param array $zz
 * @param array $parameters
 */
function mf_activities_formkit_contacts(&$zz, $parameters) {
	$zz['table'] = wrap_db_prefix($zz['table']);
	foreach ($zz['fields'] as $no => $field) {
		if (empty($zz['fields'][$no])) continue;
		$zz['fields'][$no]['export'] = false;
		// keep ID field
		if (!empty($zz['fields'][$no]['type']) AND $zz['fields'][$no]['type'] === 'id') continue; 
		$zz['fields'][$no]['hide_in_form'] = true;
		$zz['fields'][$no]['hide_in_list'] = true;
		if (empty($field['field_name'])) continue;
		if (!empty($parameters['db_values'][$zz['table'].'.'.$field['field_name']])) {
			$zz['fields'][$no]['type'] = 'hidden';
			$zz['fields'][$no]['value'] = mf_activities_formkit_value($parameters['db_values'][$zz['table'].'.'.$field['field_name']]);
		}
	}
}

/**
 * show link to participations from applicants table
 *
 * @param int $event_id
 * @return array
 */
function mf_activities_formkit_participations($event_id) {
	$def = zzform_include('participations');
	$def['type'] = 'subtable';
	$def['min_records'] = 1;
	$def['min_records_required'] = 1;
	$def['max_records'] = 1;
	$def['form_display'] = 'inline';
	$def['list_display'] = 'inline';
	foreach ($def['fields'] as $sub_no => $field) {
		if (empty($def['fields'][$sub_no])) continue;
		$def['fields'][$sub_no]['hide_in_form'] = true;
		$def['fields'][$sub_no]['hide_in_list'] = true;
		$def['fields'][$sub_no]['export'] = false;
		if (empty($field['field_name'])) continue;
		switch ($field['field_name']) {
		case 'participation_id':
			$def['fields'][$sub_no]['export'] = true;
			$def['fields'][$sub_no]['if']['export']['hide_in_list'] = false;
			$def['fields'][$sub_no]['field_sequence'] = 1;
			$def['fields'][$sub_no]['hide_in_form'] = false; // won’t be shown, but is needed
			break;
		case 'contact_id':
			$def['fields'][$sub_no]['type'] = 'foreign_key';
			break;
		case 'entry_date':
			$def['fields'][$sub_no]['hide_in_form'] = false;
			$def['fields'][$sub_no]['hide_in_list'] = false;
			$def['fields'][$sub_no]['append_next'] = false;
			$def['fields'][$sub_no]['suffix'] = false;
			$def['fields'][$sub_no]['export'] = true;
			break;
		case 'status_category_id':
			$def['fields'][$sub_no]['hide_in_form'] = false;
			$def['fields'][$sub_no]['hide_in_list'] = false;
			$def['fields'][$sub_no]['append_next'] = false;
			$def['fields'][$sub_no]['list_append_next'] = false;
			$def['fields'][$sub_no]['export'] = true;
			break;
		case 'usergroup_id':
			$def['fields'][$sub_no]['hide_in_form'] = false;
			$def['fields'][$sub_no]['hide_in_list'] = false;
			$def['fields'][$sub_no]['export'] = true;
			break;
		}
	}
	$def['sql'] = wrap_edit_sql($def['sql']
		, 'WHERE', sprintf('events.event_id = %d', $event_id)
	);
	return $def;
}

/**
 * get next available index in fields definition
 *
 * @param array $fields
 * @return int
 */
function mf_activities_formkit_no($fields) {
	$last_no = 0;
	foreach (array_keys($fields) as $no)
		if ($no > $last_no) $last_no = $no;
	return ++$last_no;
}

/**
 * normalize parameters for a form field
 *
 * @param array $parameters
 * @return array
 */
function mf_activities_formkit_normalize_parameters($parameters) {
	if (!$parameters) return [];
	foreach ($parameters as $key => $value)
		if (!$value) unset($parameters[$key]);
	return $parameters;
}

/**
 * get no. of field in table
 *
 * @param array $formfield
 * @param array $fields
 * @return int
 */
function mf_activities_formkit_field($formfield, $fields) {
	foreach ($fields as $no => $field) {
		if (empty($field['field_name'])) continue;
		if ($field['field_name'] !== $formfield['field_name']) continue;
		return $no;
	}
	wrap_error(sprintf('Configuration error: Form field %s not found in definition.', $formfield['field_name']), E_USER_ERROR);
}

/**
 * create definition for form
 *
 * @param array $formfield
 * @param int $def_no
 * @return array
 */
function mf_activities_formkit_subtable($formfield, $def_no) {
	$def = zzform_include($formfield['table']);
	$def['table'] = wrap_db_prefix($def['table']);
	$def['type'] = 'subtable';
	$def['table_name'] = $def['table'].'_'.$def_no;
	$def['form_display'] = 'lines';
	$def['min_records'] = 1;
	$def['max_records'] = $formfield['custom']['max_records'] ?? 1;
	$def['min_records_required'] = $formfield['custom']['optional'] ?? 1;
	$def['dont_show_missing'] = true; // show only individual errors
	$def['class'] = !empty($formfield['hide_in_form']) ? 'hidden' : '';
	
	$has_formfield_id = false;
	foreach ($def['fields'] as $field_no => $field) {
		if (empty($field)) continue;
		if (!empty($formfield['hide_in_form']))
			$def['fields'][$field_no]['hide_in_form'] = $formfield['hide_in_form'];
		if (empty($field['field_name'])) continue;
		switch ($field['field_name']) {
		case 'registration_id':
		case 'contact_id':
			// check before field_name, as contact_id might be field name here
			$def['fields'][$field_no]['type'] = 'foreign_key';
			break;
		case $formfield['field_name']:
			$def['fields'][$field_no]['type'] = $formfield['definition']['type'];
			$def['fields'][$field_no]['title'] = $formfield['formfield']; // for better error messages
			$def['fields'][$field_no]['maxlength'] = $formfield['custom']['maxlength'] ?? wrap_setting('maxlength_memo');
			$def['fields'][$field_no]['hide_in_list'] = true;
			$field_function = 'mf_activities_formkit_'.$formfield['definition']['type'];
			if (function_exists($field_function))
				$def['fields'][$field_no] = $field_function($def['fields'][$field_no], $formfield);
			break;
		case 'formfield_id':
			$def['fields'][$field_no]['type'] = 'hidden';
			$def['fields'][$field_no]['value'] = $formfield['formfield_id'];
			$def['fields'][$field_no]['hide_in_form'] = true;
			$def['fields'][$field_no]['def_val_ignore'] = true;
			$def['fields'][$field_no]['exclude_from_search'] = true;
			$has_formfield_id = true;
			break;
		}
		if (!empty($formfield['definition']['db_values'][$field['field_name']])) {
			$def['fields'][$field_no]['value'] = mf_activities_formkit_value($formfield['definition']['db_values'][$field['field_name']]);
			$def['fields'][$field_no]['type'] = 'hidden';
			$def['fields'][$field_no]['hide_in_form'] = true;
		}
	}
	
	if ($has_formfield_id) {
		$def['sql'] = wrap_edit_sql($def['sql'], 'WHERE',
			sprintf('formfield_id = %d', $formfield['formfield_id'])
		);
		if (!empty($def['subselect']['sql']))
			$def['subselect']['sql'] = wrap_edit_sql($def['subselect']['sql'], 'WHERE',
				sprintf('formfield_id = %d', $formfield['formfield_id'])
			);
	}

	// @todo edit_from
	// @todo edit_by
	return $def;
}

/**
 * create field with type select
 *
 * @param array $field
 * @param array $formfield
 * @return array
 */
function mf_activities_formkit_select($field, $formfield) {
	$field['null'] = true;
	$select_type = $formfield['definition']['select_type'] ?? 'enum';
	$field[$select_type] = $formfield['custom']['selection'] ?? [];
	$field['show_values_as_list'] = $formfield['custom']['show_values_as_list'] ?? $formfield['definition']['show_values_as_list'] ?? false;
	return $field;
}

/**
 * translate a field value
 * supported: ID with wrap_id()
 *
 * @param string $value
 * @return mixed (string or int)
 */
function mf_activities_formkit_value($value) {
	$content = explode(' ', $value);
	switch ($content[0]) {
	case 'ID':
		if (count($content) !== 3) return $value;
		$value = wrap_id($content[1], $content[2]);
		break;
	}
	return $value;
}

/**
 * read used usergroup_id for formkit
 *
 * @param array $parameters
 * @return int
 */
function mf_activities_formkit_usergroup($parameters) {
	return mf_activities_formkit_value(
		$parameters['db_values']['participants.usergroup_id'] ?? 'ID usergroups '.wrap_setting('activities_registration_usergroup_default')
	);
}

/**
 * hook after inserting a registration
 *
 * @param array $ops
 * @return bool
 */
function mf_activities_formkit_hook($ops) {
	$data = wrap_static('zzform', 'event');
	$event_id = $data['event_id'];
	$contact_id = 0;
	foreach ($ops['return'] as $table) {
		if ($table['table'] !== 'contacts') continue;
		$contact_id = $table['id_value'];
	}
	if (!$contact_id)
		wrap_error('Unable to find registration.', E_USER_ERROR);

	$participation_id = mf_activities_formkit_hook_participation($contact_id, $event_id, $data['form_parameters']);
	$activity_id = mf_activities_formkit_hook_activity($participation_id);
	if (empty($data['form_parameters']['no_authentication_mail']))
		mf_activities_formkit_mail_send($event_id, $contact_id, 'authentication');
	return true;
}

/**
 * link registration to event via participations
 *
 * @param int $contact_id
 * @param int $event_id
 * @param array $parameters
 * @return int
 */
function mf_activities_formkit_hook_participation($contact_id, $event_id, $parameters) {
	$values = [];
	$values['action'] = 'insert';
	$values['ids'] = [
		'contact_id', 'usergroup_id', 'event_id', 'status_category_id'
	];
	$values['POST']['contact_id'] = $contact_id;
	$values['POST']['usergroup_id'] = mf_activities_formkit_usergroup($parameters);
	$values['POST']['event_id'] = $event_id;
	$values['POST']['status_category_id'] = mf_activities_formkit_value('ID categories participation-status/subscribed');
	$values['POST']['entry_contact_id'] = $_SESSION['contact_id'] ?? $contact_id;
	$ops = zzform_multi('participations', $values);
	if (empty($ops['id']))
		wrap_error(sprintf('Unable to add participation for registration with contact ID %d.', $contact_id), E_USER_ERROR);
	return $ops['id'];
}

/**
 * link registration to event via participations
 *
 * @param int $participation_id
 * @return int
 */
function mf_activities_formkit_hook_activity($participation_id) {
	$values = [];
	$values['action'] = 'insert';
	$values['ids'] = ['participation_id', 'activity_category_id'];
	$values['POST']['participation_id'] = $participation_id;
	$values['POST']['activity_category_id'] = wrap_category_id('activities/subscribe'); // register?
	$ops = zzform_multi('activities', $values);
	if (empty($ops['id']))
		wrap_error(sprintf('Unable to add activity for participation with ID %d.', $participation_id), E_USER_ERROR);
	return $ops['id'];
}

/**
 * send form mail
 *
 * @param int $event_id
 * @param int $contact_id
 * @param string $type
 */
function mf_activities_formkit_mail_send($event_id, $contact_id, $type) {
	$value = sprintf('%d/%d/%s', $event_id, $contact_id, $type);
	$url = wrap_path('activities_formmail_send', $value, false);
	$success = wrap_job($url);
	if ($success) return true;
	return false;
}
