<?php 

/**
 * activities module
 * form script: registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


// read form placeholder if not there
if (!empty($brick['data'])) {
	$data = $brick['data'];
	$separate_page = true;
} else {
	$data = $brick['parameter'];
	$separate_page = false;
}

if (!$data['published']) wrap_quit(404);
if ($data['formtemplates_authentication_missing']) wrap_quit(503, wrap_text('Authentication mail is missing.'));
if ($data['formtemplates_confirmation_missing']) wrap_quit(503, wrap_text('Confirmation mail is missing.'));
if (empty($data['formfields'])) wrap_quit(503, wrap_text('One or more of the required form fields are missing.'));

wrap_include_files('zzform/formkit', 'activities');
$zz = mf_activities_formkit($data['event_id'], $data['form_parameters']);

$zz['record']['form_lead'] = $data['header']; // @todo this is lead, not header actually
$zz['footer']['text'] = $data['footer'];

$zz['title'] = $data['event'];
$zz['access'] = 'add_only';
$zz['hooks']['after_insert'] = 'mf_activities_formkit_hook';
$zz['vars']['event'] = $data;

$zz['setting']['zzform_autofocus'] = false;
$zz['setting']['translate_fields'] = false;
wrap_setting('contacts_details_with_label', false);

wrap_text_set('Add a record', $data['form_parameters']['legend'] ?? $data['category']);
if (!empty($data['form_parameters']['action']))
	wrap_text_set('Add record', $data['form_parameters']['action']);
wrap_text_set('Record was inserted', $data['form_parameters']['legend_insert'] ?? $data['category']);

if ($separate_page) {
	// call request script only if it is a standalone form
	$zz['page']['request'][] = 'form';
} else {
	$zz['title'] = '';
}
