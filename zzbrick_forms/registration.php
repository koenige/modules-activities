<?php 

/**
 * activities module
 * form script: registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (count($brick['vars']) !== 1) wrap_quit(404);

$sql = 'SELECT registration_id, usergroup_id
		, event_id, event
		, CONCAT(IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
		, form_id, forms.header, forms.footer, forms.lead, access, address
    FROM registrations
    LEFT JOIN events USING (event_id)
    LEFT JOIN forms USING (event_id)
    WHERE registration_hash = "%s"
    AND forms.form_category_id = %d';
$sql = sprintf($sql
	, wrap_db_escape($brick['vars'][0])
	, wrap_category_id('forms/registration')
);
$registration = wrap_db_fetch($sql);
if (!$registration) wrap_quit(404, wrap_text('There is no registration for this code.'));

$sql = 'SELECT formfield_id, formfield, explanation
		, category, area, formfields.sequence, formfields.parameters, edit_from, edit_by
	FROM formfields
	LEFT JOIN categories
		ON formfields.formfield_category_id = categories.category_id
	WHERE form_id = %d
	ORDER BY formfields.sequence';
$sql = sprintf($sql, $registration['form_id']);
$registration['formfields'] = wrap_db_fetch($sql, 'formfield_id');
if (!$registration['formfields']) wrap_quit(404, wrap_text('There is no registration for this code.'));

$values['relations'] = [];

$zz = zzform_include_table('contacts', $values);

$zz['title'] = $registration['event'].'<br>'.wrap_date($registration['duration']);
$zz['record']['form_lead'] = $registration['lead'];
$zz['explanation'] = $registration['header'];
$zz_conf['footer_text'] = $registration['footer'];

$zz['access'] = 'add_only';

$fields = [];
foreach ($zz['fields'] as $no => $field) {
	if (!$zz['fields'][$no]) continue;
	$zz['fields'][$no]['hide_in_form'] = true;
}

foreach ($registration['formfields'] as $formfield) {
	$type = 'field';
	parse_str($formfield['parameters'], $parameters);
	switch ($formfield['category']) {
	case 'Contact':
		$my_field = &$zz['fields'][2];
		break;
	case 'E-Mail':
		$my_field = &$zz['fields'][30];
		$type = 'subtable';
		break;
	case 'Address':
		$my_field = &$zz['fields'][5];
		$type = 'subtable';
		break;
	}

	$my_field['hide_in_form'] = false;
	$my_field['title'] = $formfield['formfield'];
	$my_field['explanation'] = $formfield['explanation'];
	$my_field['field_sequence'] = $formfield['sequence'];
	if ($type === 'subtable') {
		$my_field['min_records'] = 1;
		$my_field['max_records'] = 1;
		if (empty($parameters['optional']))
			$my_field['min_records_required'] = 1;
		if (!empty($parameters['value'])) {
			foreach ($parameters['value'] as $field_name => $value) {
				foreach ($my_field['fields'] as $sub_no => $subfield) {
					if (empty($subfield['field_name'])) continue;
					if ($subfield['field_name'] !== $field_name) continue;
					$my_field['fields'][$sub_no]['type'] = 'hidden';
					$my_field['fields'][$sub_no]['type_detail'] = 'select';
					$id_field = explode('_', $field_name);
					array_pop($id_field); // _id
					$id_field = array_pop($id_field);
					if (str_ends_with($id_field, 'y'))
						$id_field = substr($id_field, 0, -1).'ies';
					$my_field['fields'][$sub_no]['value'] = wrap_id($id_field, $value);
					$my_field['fields'][$sub_no]['hide_in_form'] = true;
				}
			}
		}
	} else {
		if (empty($parameters['optional']))
			$my_field['required'] = 1;
	}
}

$zz_conf['text'][$zz_setting['lang']]['Add a record'] = wrap_text('Register');
$zz_conf['text'][$zz_setting['lang']]['Add record'] = wrap_text('Submit Registration');

