<?php 

/**
 * activities module
 * form kit
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * create a form based on formfields
 *
 * @param int $event_id
 * @param string $parameters
 * @return array
 */
function mf_activities_formkit($event_id, $parameters) {
	$formfields = mf_activities_formkit_fields($event_id);
	$main_table = mf_activities_formkit_which($formfields);

	if (!$parameters) $parameters = [];
	elseif (!is_array($parameters)) $parameters = parse_str($parameters, $parameters);

	switch ($main_table) {
	case 'contacts':
		$zz = zzform_include('contacts');
		mf_activities_formkit_table($zz, $parameters);
		break;
	case 'persons':
		$zz = zzform_include('persons', [], 'forms');
		mf_activities_formkit_table($zz, $parameters);
		foreach ($zz['fields'] as $no => $field) {
			$identifier = zzform_field_identifier($field);
			switch ($identifier) {
			case 'persons':
				mf_activities_formkit_table($zz['fields'][$no], $parameters);
				$persons_no = $no;
				foreach ($field['fields'] as $sub_no => $sub_field) {
					if ($sub_field['field_name'] === 'last_name') {
						unset($zz['fields'][$no]['fields'][$sub_no]['list_append_next']);
						unset($zz['fields'][$no]['fields'][$sub_no]['unless']['export_mode']['list_append_next']);
					}
				}
				break;
			}
		}
	}

	$no = mf_activities_formkit_no($zz['fields']);
	
	$area = '';
	$nos = [];
	foreach ($formfields as $formfield_id => $formfield) {
		if (empty($formfield['definition']['db_field'])) continue; // @todo, captcha
		// @todo show_without_login=0 and form = login: continue;
		// if (form === login AND empty($formfield['definition']['show_without_login'])) continue;
		list($formfield['table'], $formfield['field_name']) = explode('.', $formfield['definition']['db_field']);
		if ($formfield['table'] === $zz['table']) {
			$my_no = mf_activities_formkit_field($formfield, $zz['fields']);
			$zz['fields'][$my_no]['type'] = $formfield['definition']['type'];
			$my_field = &$zz['fields'][$my_no];
		} elseif (wrap_db_prefix($formfield['table']) === wrap_db_prefix('/*_PREFIX_*/persons')) {
			$zz['fields'][$persons_no]['hide_in_form'] = false;
			$my_no = mf_activities_formkit_field($formfield, $zz['fields'][$persons_no]['fields']);
			$my_field = &$zz['fields'][$persons_no]['fields'][$my_no];
		} else {
			$zz['fields'][$no] = mf_activities_formkit_subtable($formfield, $no, $nos);
			$my_field = &$zz['fields'][$no];
			$nos[$formfield_id] = $no;
			if (wrap_db_prefix($formfield['table']) === wrap_db_prefix('/*_PREFIX_*/media'))
				$formfield['custom']['hide_in_list'] = true;
		}
		$my_field['title'] = $formfield['formfield'];
		if (empty($formfield['definition']['selection_from_explanation']))
			$my_field['explanation'] = $formfield['explanation'];
		$my_field['hide_in_form'] = false;
		$my_field['export'] = true;
		$my_field['hide_in_list'] = $formfield['custom']['hide_in_list'] ?? $formfield['definition']['hide_in_list'] ?? false;
		if ($formfield['area'] AND $formfield['area'] !== $area) {
			$my_field['separator_before'] = 'text <h3>'.$formfield['area'].'</h3>';
			$area = $formfield['area'];
		}
		$my_field['field_sequence'] = $no;
		$no++;
	}
	
	// last_update
	$zz['fields'][99]['hide_in_form'] = true;
	$zz['fields'][99]['field_sequence'] = $no;
	return $zz;
}

/**
 * get form fields for event
 *
 * @param int $event_id
 * @return array
 */
function mf_activities_formkit_fields($event_id) {
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

	$params = ['custom', 'definition'];
	foreach ($formfields as $formfield_id => $formfield) {
		foreach ($params as $param) {
			if (!$formfield[$param]) continue;
			parse_str($formfield[$param], $formfield[$param]);
			$formfields[$formfield_id][$param]
				= mf_activities_formkit_normalize_parameters($formfield[$param]);
		}
		$type = $formfields[$formfield_id]['definition']['type'] ?? '';
		if ($type === 'upload') {
			$formfields[$formfield_id]['definition']['main_medium_id']
				= mf_activities_formkit_upload_folder($event_id);
			// add extra formfield for contacts_media JOIN
			$formfield['area'] = '';
			$formfield['explanation'] = '';
			$formfield['definition'] = [
				'db_field' => 'contacts_media.medium_id',
				'type' => 'foreign_id'
			];
			$formfields[] = $formfield;
		}
	}
	return $formfields;
}

/**
 * get (and create) upload folder for event
 *
 * @param int $event_id
 * @return int
 */
function mf_activities_formkit_upload_folder($event_id) {
	$sql = 'SELECT identifier FROM events WHERE event_id = %d';
	$sql = sprintf($sql, $event_id);
	$identifier = wrap_db_fetch($sql, '', 'single value');
	if (!$identifier) return 0;
	
	if ($folder = wrap_setting('activities_form_upload_folder'))
	    $folder = sprintf('%s/%s', $folder, $identifier);
	else
		$folder = $identifier;

	wrap_include('zzform/batch', 'media');	
	return mf_media_folder($folder);
}

/**
 * determine which table or form should be used as main table
 *
 * @param array $formfields
 * @return string
 */
function mf_activities_formkit_which($formfields) {
	foreach ($formfields as $field) {
		if (empty($field['definition']['db_field'])) continue;
		if (str_starts_with($field['definition']['db_field'], 'persons.'))
			return 'persons';
	}
	return 'contacts';
}

/**
 * prepare main tables (contacts, persons)
 * hide fields from list, record, export, set values
 *
 * @param array $zz
 * @param array $parameters
 */
function mf_activities_formkit_table(&$zz, $parameters) {
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
			$def['fields'][$sub_no]['if']['export_mode']['hide_in_list'] = false;
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
	foreach ($parameters as $key => $value) {
		if (!$value) unset($parameters[$key]);
		$parameters[$key] = wrap_setting_value($value);
	}
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
 * @param array $nos
 * @return array
 */
function mf_activities_formkit_subtable($formfield, $def_no, $nos) {
	$script = str_replace('_', '-', $formfield['table']);
	$def = zzform_include($script);
	$def['table'] = wrap_db_prefix($def['table']);
	$def['type'] = 'subtable';
	$def['table_name'] = $def['table'].'_'.$def_no;
	$def['form_display'] = 'lines';
	$def['min_records'] = 1;
	$def['max_records'] = $formfield['custom']['max_records'] ?? 1;
	$optional = $formfield['custom']['optional'] ?? 0;
	$def['min_records_required'] = !$optional;
	$def['dont_show_missing'] = true; // show only individual errors
	$def['class'] = !empty($formfield['hide_in_form']) ? 'hidden' : '';
	
	switch ($formfield['table']) {
		case 'media': $def = mf_activities_formkit_media($formfield, $def); break;
		case 'contacts_media': $def = mf_activities_formkit_contacts_media($formfield, $def, $nos); break;
	}
	
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
	$select_type = $formfield['definition']['select_type'] ?? $formfield['custom']['select_type'] ?? 'enum';
	if (!empty($formfield['definition']['selection_from_explanation']))
		$field[$select_type] = [trim(str_replace("\n", " ", $formfield['explanation']))];
	else {
		$field[$select_type] = mf_activities_formkit_selection($formfield['custom']['selection'] ?? $formfield['definition']['selection'] ?? []);
	}
	$field['show_values_as_list'] = $formfield['custom']['show_values_as_list'] ?? $formfield['definition']['show_values_as_list'] ?? false;
	return $field;
}

/**
 * format selection entry as array
 *
 * @param array $selections
 * @return array
 */
function mf_activities_formkit_selection($selections) {
	if (array_key_exists(wrap_setting('lang'), $selections))
		$selections = $selections[wrap_setting('lang')];
	if (!is_array($selections))
		$selections = explode(',', $selections);
	foreach ($selections as $index => $selection) {
		$selection = trim($selection);
		$selections[$index] = wrap_text($selection);
	}
	return $selections;
}

/**
 * prepare media table, hide fields
 *
 * @param array $formfield
 * @param array $def
 * @return array
 */
function mf_activities_formkit_media($formfield, $def) {
	$def['records_depend_on_upload'] = true;
	$def['type'] = 'foreign_table';
	// no background uploads via this form, @todo
	wrap_setting('zzform_upload_background_thumbnails', false);

	foreach ($def['fields'] as $no => $field) {
		if (empty($field['field_name'])) {
			// subtables, not needed here
			unset($def['fields'][$no]);
			continue;
		}
		switch ($field['field_name']) {
		case 'main_medium_id':
			$def['fields'][$no]['type'] = 'hidden';
			$def['fields'][$no]['type_detail'] = 'select';
			$def['fields'][$no]['value'] = $formfield['custom']['main_medium_id']
				?? $formfield['definition']['main_medium_id'];
			$def['fields'][$no]['hide_in_form'] = true;
			break;
		case 'title':
			$def['fields'][$no]['dont_show_missing'] = true;
			$def['fields'][$no]['hide_in_form'] = true;
		case 'image':
			$def['fields'][$no]['image'][0]['required'] = false;
			$def['fields'][$no]['show_title'] = false;
			$filetypes = $formfield['custom']['input_filetypes']
				?? $formfield['definition']['input_filetypes'] ?? [];
			if ($filetypes)
				$def['fields'][$no]['input_filetypes'] = $filetypes;
			$max_filesize = $formfield['custom']['upload_max_filesize']
				?? $formfield['definition']['upload_max_filesize'] ?? [];
			if ($max_filesize)
				$def['fields'][$no]['upload_max_filesize'] = $max_filesize;
			break;
		case 'published':
			$def['fields'][$no]['type'] = 'hidden';
			$def['fields'][$no]['type_detail'] = 'select';
			$def['fields'][$no]['value'] = 'no';
			$def['fields'][$no]['hide_in_form'] = true;
			break;
		default:
			$def['fields'][$no]['hide_in_form'] = true;
			$def['fields'][$no]['for_action_ignore'] = true;
			break;
		}
	}
	$joins = mf_formkit_joins($formfield['definition']['db_joins'] ?? []);
	foreach ($joins as $join) {
		if (strstr($join, 'filetypes')) continue;
		$def['sql'] = wrap_edit_sql($def['sql'], 'JOIN', $join);
	}
	$def['foreign_key_field_name'] = 'contacts_media.contact_id';
	return $def;
}

/**
 * prepare contacts_media table for linking
 *
 * @param array $formfield
 * @param array $def
 * @return array
 */
function mf_activities_formkit_contacts_media($formfield, $def, $nos) {
	$def['show_title'] = false;
	foreach ($def['fields'] as $subno => $subfield) {
		if (empty($subfield['field_name'])) continue;
		switch ($subfield['field_name']) {
			case 'medium_id':
				$def['fields'][$subno]['type_detail'] = 'select';
				$def['fields'][$subno]['foreign_id_field'] = $nos[$formfield['formfield_id']];
				$def['fields'][$subno]['hide_in_form'] = true;
				break;
			case 'sequence':
				// show as hidden field so record is not ignored by zzform
				// @todo solve this in zzform() and remove this case
				$def['fields'][$subno]['type'] = 'hidden';
				$def['fields'][$subno]['value'] = 1;
				$def['fields'][$subno]['class'] = 'hidden';
				break;
			case 'image':
				$def['fields'][$subno]['hide_in_form'] = true;
				break;
		}
	}
	return $def;
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
	if (!empty($parameters['db_values']['participants.usergroup_id']))
		return mf_activities_formkit_value($parameters['db_values']['participants.usergroup_id']);
	return wrap_id('usergroups', wrap_setting('activities_registration_usergroup_default'));
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
	$line = [
		'contact_id' => $contact_id,
		'usergroup_id' => mf_activities_formkit_usergroup($parameters),
		'event_id' => $event_id,
		'status_category_id' => wrap_category_id('participation-status/subscribed'),
		'entry_contact_id' => $_SESSION['contact_id'] ?? $contact_id
	];
	if (wrap_category_id('participations/registration', 'check')) {
		$line['participations_categories_'.wrap_category_id('participations/registration')][]['category_id']
			= wrap_category_id('participations/registration/direct');
	}
	return zzform_insert('participations', $line, E_USER_ERROR);
}

/**
 * link registration to event via participations
 *
 * @param int $participation_id
 * @return int
 */
function mf_activities_formkit_hook_activity($participation_id) {
	$line = [
		'participation_id' => $participation_id,
		'activity_category_id' => wrap_category_id('activities/subscribe') // register?
	];
	return zzform_insert('activities', $line, E_USER_ERROR);
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
	if (!$url) {
		wrap_error('No path for `activities_formmail_send` found.', E_USER_WARNING);
		return false;
	}
	$success = wrap_job($url);
	if ($success) return true;
	return false;
}
