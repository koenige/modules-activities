<?php 

/**
 * activities module
 * form kit
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * create a form based on formfields
 *
 * @param array $zz
 * @param int $event_id
 * @return array
 */
function mf_activities_formkit($zz, $event_id) {
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
	
	$last_update = $zz['fields'][99];
	unset($zz['fields'][99]);
	$no = mf_activities_formkit_no($zz['fields']);
	
	foreach ($formfields as $formfield) {
		if (empty($formfield['definition']['db_field'])) continue; // @todo, captcha
		// @todo hide_behind_login=1 and form = login: continue;
		$formfield['custom'] = mf_activities_formkit_normalize_parameters($formfield['custom']);
		$zz['fields'][$no] = mf_activities_formkit_subtable($formfield, $no);
		$zz['fields'][$no]['title'] = $formfield['formfield'];
		$zz['fields'][$no]['explanation'] = $formfield['explanation'];
		$zz['fields'][$no]['hide_in_form'] = false;
		$no++;
	}
	
	$last_update['hide_in_form'] = true;
	$zz['fields'][] = $last_update;
	return $zz;
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
 * create definition for form
 *
 * @param array $formfield
 * @param int $def_no
 * @return array
 */
function mf_activities_formkit_subtable($formfield, $def_no) {
	static $area;
	if (empty($area)) $area = '';
	
	list($table, $field_name) = explode('.', $formfield['definition']['db_field']);
	$def = zzform_include_table($table);
	$def['type'] = 'subtable';
	$def['table_name'] = $def['table'].'_'.$def_no;
	$def['form_display'] = 'lines';
	$def['min_records'] = 1;
	$def['max_records'] = $formfield['custom']['max_records'] ?? 1;
	$def['min_records_required'] = $formfield['custom']['optional'] ?? 1;
	$def['dont_show_missing'] = true; // show only individual errors
	if ($formfield['area'] AND $formfield['area'] !== $area) {
		$def['separator_before'] = 'text <h3><strong>'.$formfield['area'].'</strong></h3>';
		$area = $formfield['area'];
	}

	$has_formfield_id = false;
	foreach ($def['fields'] as $field_no => $field) {
		switch ($field['field_name']) {
		case $field_name:
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
		case 'registration_id':
			$def['fields'][$field_no]['type'] = 'foreign_key';
			break;
		}
	}
	
	if ($has_formfield_id) {
		$def['sql'] = wrap_edit_sql($def['sql'], 'WHERE',
			sprintf('formfield_id = %d', $formfield['formfield_id'])
		);
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

