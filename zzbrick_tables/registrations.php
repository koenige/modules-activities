<?php 

/**
 * activities module
 * table script: registrations
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Registrations';
$zz['table'] = '/*_PREFIX_*/registrations';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'registration_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id, identifier FROM events';
$zz['fields'][2]['if']['where']['hide_in_form'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/registrations.*
	FROM /*_PREFIX_*/registrations
	LEFT JOIN /*_PREFIX_*/events USING (event_id)
';
$zz['sqlorder'] = ' ORDER BY IFNULL(events.date_begin, events.date_end), events.identifier, registration_id';
