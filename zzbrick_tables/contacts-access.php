<?php 

/**
 * activities module
 * table script: contacts access
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021, 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Access to Contact Details';
$zz['table'] = '/*_PREFIX_*/contacts_access';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_access_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT /*_PREFIX_*/contacts.contact_id
		, /*_PREFIX_*/contacts.contact
		, identifier
	FROM /*_PREFIX_*/contacts 
	ORDER BY /*_PREFIX_*/contacts.identifier';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['exclude_from_search'] = true;
$zz['fields'][2]['unique_ignore'] = ['identifier'];

$zz['fields'][3]['field_name'] = 'usergroup_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT usergroup_id, usergroup 
	FROM /*_PREFIX_*/usergroups
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/usergroups.usergroup_category_id = /*_PREFIX_*/categories.category_id
	WHERE /*_PREFIX_*/categories.parameters LIKE "%%&contacts_access=1%%"
	ORDER BY usergroup';
$zz['fields'][3]['display_field'] = 'usergroup';
$zz['fields'][3]['character_set'] = 'utf8';

$zz['fields'][4]['title'] = 'Property';
$zz['fields'][4]['field_name'] = 'property_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = 'SELECT categories.category_id
		, categories.category
		, main_categories.category AS main_category
	FROM categories
	LEFT JOIN categories main_categories
		ON categories.main_category_id = main_categories.category_id
	WHERE main_categories.parameters LIKE "%&access_property=1%"
	ORDER BY main_categories.category, category';
$zz['fields'][4]['display_field'] = 'property_category';
$zz['fields'][4]['search'] = 'properties.category';
$zz['fields'][4]['character_set'] = 'utf8';
$zz['fields'][4]['group'] = 'main_category';

$zz['fields'][5]['title'] = 'Access';
$zz['fields'][5]['field_name'] = 'access_category_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = 'SELECT categories.category_id
		, categories.category
	FROM categories
	WHERE parameters LIKE "%%&contacts_access=1%%"
	AND main_category_id = /*_ID categories access _*/
	ORDER BY category';
$zz['fields'][5]['display_field'] = 'access_category';
$zz['fields'][5]['search'] = '/*_PREFIX_*/categories.category';

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT /*_PREFIX_*/contacts_access.*
		, /*_PREFIX_*/usergroups.usergroup
		, /*_PREFIX_*/categories.category AS access_category
		, /*_PREFIX_*/contacts.contact
		, properties.category AS property_category
	FROM /*_PREFIX_*/contacts_access
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts_access.access_category_id = /*_PREFIX_*/categories.category_id
	LEFT JOIN /*_PREFIX_*/categories properties
		ON /*_PREFIX_*/contacts_access.property_category_id = properties.category_id
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.identifier, /*_PREFIX_*/usergroups.identifier, properties.path';
