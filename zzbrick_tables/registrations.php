<?php 

/**
 * activities module
 * table script: registrations
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Registrations';
$zz['table'] = 'registrations';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'registration_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'usergroup_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT usergroup_id, usergroup
	FROM usergroups
	ORDER BY usergroup';
$zz['fields'][2]['display_field'] = 'usergroup';

$zz['fields'][3]['field_name'] = 'event_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT event_id, event, main_event_id
	FROM events
	WHERE ISNULL(events.main_event_id)
	ORDER BY event';
$zz['fields'][3]['display_field'] = 'event';

$zz['fields'][4]['title'] = 'Organisation';
$zz['fields'][4]['field_name'] = 'organisation_contact_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	WHERE categories.parameters LIKE "%&contacts_general=1%"
	ORDER BY identifier';
$zz['fields'][4]['display_field'] = 'contact';
$zz['fields'][4]['sql_character_set'][1] = 'utf8';
$zz['fields'][4]['if']['where']['hide_in_form'] = true;
$zz['fields'][4]['if']['where']['hide_in_list'] = true;
$zz['fields'][4]['select_dont_force_single_value'] = true;
$zz['fields'][4]['hide_in_list_if_empty'] = true;

$zz['fields'][5]['title'] = 'Min.';
$zz['fields'][5]['field_name'] = 'min_participants';
$zz['fields'][5]['type'] = 'number';
$zz['fields'][5]['hide_in_list'] = true;

$zz['fields'][6]['title'] = 'Max.';
$zz['fields'][6]['field_name'] = 'max_participants';
$zz['fields'][6]['type'] = 'number';
$zz['fields'][6]['hide_in_list'] = true;

$zz['fields'][7]['title'] = 'Waiting';
$zz['fields'][7]['field_name'] = 'waiting_participants';
$zz['fields'][7]['type'] = 'number';
$zz['fields'][7]['hide_in_list'] = true;

$zz['fields'][8]['title'] = 'Remaining';
$zz['fields'][8]['field_name'] = 'show_remaining_from';
$zz['fields'][8]['type'] = 'number';
$zz['fields'][8]['hide_in_list'] = true;

$zz['fields'][9]['title'] = 'Hash';
$zz['fields'][9]['field_name'] = 'registration_hash';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['type'] = 'hidden';
$zz['fields'][9]['class'] = 'hidden';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['function'] = 'mf_activities_random_hash_usergroups';
$zz['fields'][9]['fields'] = ['registration_hash'];
$zz['fields'][9]['export'] = false;

$zz['fields'][10]['field_name'] = 'parameters';
$zz['fields'][10]['type'] = 'parameter';
$zz['fields'][10]['hide_in_list'] = true;


$zz['sql'] = 'SELECT registrations.*
		, usergroups.usergroup
		, events.event
		, contacts.contact
	FROM registrations
	LEFT JOIN usergroups USING (usergroup_id)
	LEFT JOIN events USING (event_id)
	LEFT JOIN contacts
		ON contacts.contact_id = registrations.organisation_contact_id
';
$zz['sqlorder'] = ' ORDER BY events.date_begin DESC, events.time_begin DESC,
	events.identifier, usergroups.identifier';
