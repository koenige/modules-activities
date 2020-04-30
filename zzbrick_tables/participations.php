<?php 

/**
 * Zugzwang Project
 * Table with participations
 *
 * http://www.zugzwang.org/modules/registrations
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Participations';
$zz['table'] = '/*_PREFIX_*/participations';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'participation_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM contacts ORDER BY contact';
$zz['fields'][2]['display_field'] = 'contact';

$zz['fields'][3]['field_name'] = 'usergroup_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT usergroup_id, usergroup, category
	FROM usergroups
	LEFT JOIN categories
		ON usergroups.usergroup_category_id = categories.category_id
	ORDER BY identifier';
$zz['fields'][3]['display_field'] = 'usergroup';
$zz['fields'][3]['group'] = 'category';

$zz['fields'][4]['field_name'] = 'date_begin';
$zz['fields'][4]['type'] = 'date';

$zz['fields'][5]['field_name'] = 'date_end';
$zz['fields'][5]['type'] = 'date';

$zz['fields'][6]['title'] = 'Category';
$zz['fields'][6]['field_name'] = 'status_category_id';
$zz['fields'][6]['type'] = 'select';
$zz['fields'][6]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('participation-status')
);
$zz['fields'][6]['key_field_name'] = 'category_id';
$zz['fields'][6]['if']['where']['hide_in_form'] = true;
$zz['fields'][6]['if']['where']['hide_in_list'] = true;
$zz['fields'][6]['display_field'] = 'category';

$zz['fields'][7]['field_name'] = 'remarks';
$zz['fields'][7]['hide_in_list'] = true;
$zz['fields'][7]['type'] = 'memo';
$zz['fields'][7]['rows'] = 3;
$zz['fields'][7]['explanation'] = '(internal remarks only)';

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/participations.*, contact, usergroup, category
	FROM /*_PREFIX_*/participations
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/usergroups.usergroup_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/usergroups.identifier, /*_PREFIX_*/contacts.identifier, date_begin';
