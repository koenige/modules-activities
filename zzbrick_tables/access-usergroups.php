<?php 

/**
 * activities module
 * table script: access/usergroups
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Usergroups per Access Right';
$zz['table'] = '/*_PREFIX_*/access_usergroups';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'access_usergroup_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'access_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT access_id, access_key, explanation
	FROM access
	ORDER BY access_key';
$zz['fields'][2]['display_field'] = 'access_key';

$zz['fields'][3]['title'] = 'Usergroup';
$zz['fields'][3]['field_name'] = 'usergroup_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT usergroup_id, usergroup
	FROM usergroups
	ORDER BY identifier';
$zz['fields'][3]['display_field'] = 'usergroup';

$zz['fields'][4]['title'] = 'Restrictions?';
$zz['fields'][4]['field_name'] = 'restricted_to_field';
$zz['fields'][4]['explanation'] = 'Field name if access for an item is restricted to a participation with this ID';

$zz['subselect']['sql'] = 'SELECT access_id, usergroup, restricted_to_field, usergroup_id
	FROM /*_PREFIX_*/usergroups
	LEFT JOIN /*_PREFIX_*/access_usergroups USING (usergroup_id)
';
$zz['subselect']['sql_translate'] = ['usergroup_id' => 'usergroup'];
$zz['subselect']['sql_ignore'] = ['usergroup_id'];
$zz['subselect']['concat_fields'] = ', ';

$zz['sql'] = 'SELECT /*_PREFIX_*/access_usergroups.*, access_key, usergroup
	FROM /*_PREFIX_*/access_usergroups
	LEFT JOIN /*_PREFIX_*/access USING (access_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
';
$zz['sqlorder'] = ' ORDER BY access_key, usergroup';
