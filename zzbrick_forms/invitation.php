<?php 

/**
 * activities module
 * form script: invitation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (count($brick['vars']) !== 1) wrap_quit(404);

$sql = 'SELECT invitation_id, usergroup_id
		, event_id, event
		, CONCAT(IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
		, form_id, forms.header, forms.footer, forms.lead, access, address
    FROM invitations
    LEFT JOIN events USING (event_id)
    LEFT JOIN forms USING (event_id)
    WHERE invitation_hash = "%s"
    AND forms.form_category_id = /*_ID categories forms/registration _*/';
$sql = sprintf($sql, wrap_db_escape($brick['vars'][0]));
$invitation = wrap_db_fetch($sql);
if (!$invitation) wrap_quit(404, wrap_text('There is no invitation for this code.'));

$sql = 'SELECT formfield_id, formfield, explanation
		, category, area, formfields.sequence, formfields.parameters, edit_from, edit_by
	FROM formfields
	LEFT JOIN categories
		ON formfields.formfield_category_id = categories.category_id
	WHERE form_id = %d
	ORDER BY formfields.sequence';
$sql = sprintf($sql, $invitation['form_id']);
$invitation['formfields'] = wrap_db_fetch($sql, 'formfield_id');
if (!$invitation['formfields']) wrap_quit(404, wrap_text('There is no invitation for this code.'));

$values['relations'] = [];

$zz = zzform_include('contacts', $values, 'forms');

$zz['title'] = $invitation['event'].' <br>'.wrap_date($invitation['duration']);
$zz['record']['form_lead'] = $invitation['lead'];
$zz['explanation'] = $invitation['header'];
$zz['footer']['text'] = $invitation['footer'];
$zz['footer']['text_insert'] = markdown(wrap_text('We just sent you an e-mail. Please click on the link inside it to confirm your registration.'));

$zz['access'] = 'add_only';

$fields = [];
foreach ($zz['fields'] as $no => $field) {
	if (!$field) continue;
	$zz['fields'][$no]['hide_in_form'] = true;
	if (empty($field['field_name'])) continue;
	if ($field['field_name'] === 'contact_category_id') {
		$zz['fields'][$no]['type'] = 'hidden';
		$zz['fields'][$no]['value'] = wrap_category_id('contact/person');
	}
}

foreach ($invitation['formfields'] as $formfield) {
	$type = 'field';
	if ($formfield['parameters'])
		parse_str($formfield['parameters'], $formfield['parameters']);
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
		$my_field['fields'][7]['hide_in_form'] = true; // latitude
		$my_field['fields'][8]['hide_in_form'] = true; // longitude
		$my_field['fields'][6]['for_action_ignore'] = true; // country_id
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
		if (empty($formfield['parameters']['optional']))
			$my_field['min_records_required'] = 1;
		if (!empty($formfield['parameters']['value'])) {
			foreach ($formfield['parameters']['value'] as $field_name => $value) {
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
		if (empty($formfield['parameters']['optional']))
			$my_field['required'] = 1;
	}
}

$zz['fields'][198] = zzform_include('participations');
$zz['fields'][198]['type'] = 'subtable';
$zz['fields'][198]['min_records'] = 1;
$zz['fields'][198]['max_records'] = 1;
$zz['fields'][198]['fields'][2]['type'] = 'foreign_key';
// usergroup_id
$zz['fields'][198]['fields'][3]['type'] = 'hidden';
$zz['fields'][198]['fields'][3]['type_detail'] = 'select';
$zz['fields'][198]['fields'][3]['hide_in_form'] = true;
$zz['fields'][198]['fields'][3]['value'] = $invitation['usergroup_id'];
// date_begin
$zz['fields'][198]['fields'][4]['type'] = 'hidden';
$zz['fields'][198]['fields'][4]['value'] = date('Y-m-d');
// date_end
$zz['fields'][198]['fields'][5]['hide_in_form'] = true;
// status_category_id
$zz['fields'][198]['fields'][6]['type'] = 'hidden';
$zz['fields'][198]['fields'][6]['type_detail'] = 'select';
$zz['fields'][198]['fields'][6]['value'] = wrap_category_id('participation-status/subscribed');
$zz['fields'][198]['fields'][6]['dont_show_missing'] = true; // category
// role
$zz['fields'][198]['fields'][11]['hide_in_form'] = true;
// sequence
$zz['fields'][198]['fields'][9]['hide_in_form'] = true;
// remarks
$zz['fields'][198]['fields'][7]['hide_in_form'] = true;
// hash
$zz['fields'][198]['fields'][8]['hide_in_form'] = true;
$zz['fields'][198]['fields'][8]['dont_show_missing'] = true; 
// event_id
$zz['fields'][198]['fields'][12]['type'] = 'hidden';
$zz['fields'][198]['fields'][12]['value'] = $invitation['event_id'];

$zz['fields'][198]['fields'][99]['hide_in_form'] = true;
$zz['fields'][198]['class'] = 'hidden';

wrap_text_set('Add a record', 'Register');
wrap_text_set('Add record', 'Submit Registration');
wrap_text_set('Record was inserted', 'The registration has been sent successfully!');

if (!empty($_POST['contact']))
	$zz['setting']['log_username_default'] = strtolower(wrap_filename($_POST['contact'], '.'));

$zz['hooks']['after_insert'][] = 'mf_activities_confirm_registration';
