<?php 

/**
 * activities module
 * form script: access rights per contact
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['contact_id'])) wrap_quit(404);

//$zz = zzform_include('contacts-access');
//$zz['where']['contact_id'] = $brick['data']['contact_id'];

$sql = 'SELECT category_id FROM categories
	WHERE parameters LIKE "%&contacts_access=1%"';
$categories = wrap_db_fetch($sql, 'category_id');
if (!$categories)
	wrap_quit(404, wrap_text('No categories for access rights are defined (via `%s`)', ['values' => ['contacts_access=1']]));

$zz = zzform_include('usergroups');

$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE',
	sprintf('usergroup_category_id IN (%s)', implode(',', array_keys($categories)))
);

$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE',
	'active = "yes"'
);
$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE',
	'(ISNULL(usergroups.parameters) OR usergroups.parameters NOT LIKE "%&activities_contacts_access_hidden=1%")'
);

unset($zz['filter']);
unset($zz['list']['group']);

$zz['access'] = 'edit_details_only';

foreach ($zz['fields'] as $no => $field) {
	$field_name = $field['field_name'] ?? '';
	switch ($field_name) {
	case 'last_update':
	case 'parameters':
	case 'active':
	case 'sequence':
	case 'usergroup_category_id':
	case 'identifier':
		$zz['fields'][$no]['hide_in_form'] = true;
		$zz['fields'][$no]['hide_in_list'] = true;
		break;
	}
}

$zz['fields'][90] = zzform_include('contacts-access');
$zz['fields'][90]['title'] = 'Access Rights';
$zz['fields'][90]['type'] = 'subtable';
$zz['fields'][90]['sql'] = wrap_edit_sql($zz['fields'][90]['sql'],
	'WHERE', sprintf('contact_id = %d', $brick['data']['contact_id'])
);
$zz['fields'][90]['form_display'] = 'lines';
$zz['fields'][90]['fields'][2]['type'] = 'hidden';
$zz['fields'][90]['fields'][2]['hide_in_form'] = true;
$zz['fields'][90]['fields'][2]['value'] = $brick['data']['contact_id'];
$zz['fields'][90]['fields'][2]['for_action_ignore'] = true;
$zz['fields'][90]['fields'][3]['type'] = 'foreign_key';
$zz['fields'][90]['fields'][5]['for_action_ignore'] = true;

$zz['fields'][90]['subselect']['sql'] = sprintf('SELECT usergroup_id
		, properties.category AS property_category
		, access.category AS access_category
	FROM contacts_access
	LEFT JOIN categories access
		ON access.category_id = contacts_access.access_category_id
	LEFT JOIN categories properties
		ON properties.category_id = contacts_access.property_category_id
	WHERE contact_id = %d', $brick['data']['contact_id']);
$zz['fields'][90]['subselect']['field_prefix'][0] = '<em>';
$zz['fields'][90]['subselect']['field_suffix'][0] = '</em>: ';
$zz['fields'][90]['subselect']['concat_rows'] = '<br>';

$zz['title'] = 'Access to Contact Details';

$zz['subtitle']['text'] = $brick['data']['contact'];

$zz['page']['referer'] = '../';

