<?php 

/**
 * activities module
 * table script: participations in usergroups
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['vars'][0])) wrap_quit(404);
if (empty($brick['data_context']['usergroup_id'])) {
	// direct access: get data
	wrap_include('usergroups', 'activities');
	$data = mf_activities_usergroup($brick['vars'][0]);
	$use_template = false;
} else {
	$data = $brick['data_context'];
	$use_template = true; // via template
}

$zz = zzform_include('activities/participations');

if (!empty($data['parameters']['access'])) {
	$zz['access'] = $data['parameters']['access'];
} elseif (!wrap_access('activities_participants_delete')) {
	if (wrap_access('activities_participants_edit'))
		$zz['access'] = 'show_edit_add';
	else
		$zz['access'] = 'none';
}

$zz['where']['usergroup_id'] = $data['usergroup_id'];
$zz['title'] = $data['usergroup'];
if (!$use_template)
	$zz['explanation'] = markdown($data['description']);

$zz['fields'][2]['type'] = 'write_once';

$zz['fields'][9]['type'] = 'sequence';

if (!empty($data['parameters']['hide']['status_category_id']))
	$zz['fields'][6]['hide_in_list'] = true;

$zz['filter'][1]['sql'] = wrap_edit_sql(
	$zz['filter'][1]['sql'], 'WHERE', sprintf('usergroup_id = %d', $data['usergroup_id'])
);

// search: postcode
$zz['fields'][13]['field_name'] = 'postcode';
$zz['fields'][13]['type'] = 'display';
$zz['fields'][13]['hide_in_list'] = true;
$zz['fields'][13]['hide_in_form'] = true;
$zz['fields'][13]['search'] = '(SELECT postcode FROM addresses WHERE addresses.contact_id = participations.contact_id LIMIT 1)';


if (!empty($data['parameters']['filter_mail'])) {
	$zz['filter'][3]['title'] = wrap_text('E-Mail');
	$zz['filter'][3]['identifier'] = 'mail';
	$zz['filter'][3]['type'] = 'list';
	$zz['filter'][3]['where'] = 'identification';
	$zz['filter'][3]['sql_join'] = 'LEFT JOIN /*_PREFIX_*/contactdetails
		ON /*_PREFIX_*/contactdetails.contact_id = /*_PREFIX_*/participations.contact_id
		AND provider_category_id = /*_ID categories provider/e-mail_*/';
	$zz['filter'][3]['selection']['!NULL'] = wrap_text('with E-Mail');
	$zz['filter'][3]['selection']['NULL'] = wrap_text('without E-Mail');
}

if ($use_template) {
	$zz['dont_show_h1'] = true;
	$zz['list']['no_add_above'] = true;
}
