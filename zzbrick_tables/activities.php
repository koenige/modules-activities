<?php 

/**
 * activities module
 * table script: activities
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Activities';
$zz['table'] = '/*_PREFIX_*/activities';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'activity_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'participation_id';
$zz['fields'][2]['type'] = 'write_once';
$zz['fields'][2]['type_detail'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT participation_id, contact, identifier
	FROM participations
	LEFT JOIN contacts USING (contact_id)
	ORDER BY contact';
$zz['fields'][2]['display_field'] = 'contact';

$zz['fields'][3]['title'] = 'Category';
$zz['fields'][3]['field_name'] = 'activity_category_id';
$zz['fields'][3]['type'] = 'write_once';
$zz['fields'][3]['type_detail'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT category_id, category, main_category_id
	FROM categories
	ORDER BY path';
$zz['fields'][3]['display_field'] = 'category';
$zz['fields'][3]['show_hierarchy'] = 'main_category_id';
$zz['fields'][3]['show_hierarchy_subtree'] = wrap_category_id('activities');

$zz['fields'][4]['title'] = 'Date';
$zz['fields'][4]['field_name'] = 'activity_date';
$zz['fields'][4]['type'] = 'write_once';
$zz['fields'][4]['type_detail'] = 'datetime';
$zz['fields'][4]['unless']['export_mode']['list_prefix'] = '<small style="color: #999;">';
$zz['fields'][4]['unless']['export_mode']['list_suffix'] = '<br>';
$zz['fields'][4]['unless']['export_mode']['list_append_next'] = true;
$zz['fields'][4]['default'] = date('Y-m-d H:i:s');

$zz['fields'][5]['title'] = 'Activity IP';
$zz['fields'][5]['field_name'] = 'activity_ip';
$zz['fields'][5]['type'] = 'write_once';
$zz['fields'][5]['type_detail'] = 'ip';
$zz['fields'][5]['default'] = wrap_setting('remote_ip');
$zz['fields'][5]['export'] = false;
$zz['fields'][5]['unless']['export_mode']['list_suffix'] = '</small>';

$zz['fields'][6]['title'] = 'URI';
$zz['fields'][6]['field_name'] = 'activity_uri';
$zz['fields'][6]['type'] = 'write_once';
$zz['fields'][6]['type_detail'] = 'url';
$zz['fields'][6]['default'] = wrap_setting('request_uri');
$zz['fields'][6]['list_append_next'] = true;

$zz['fields'][7]['field_name'] = 'comment';
$zz['fields'][7]['list_prefix'] = '<br>';

$zz['sql'] = 'SELECT /*_PREFIX_*/activities.*, contact, category
	FROM /*_PREFIX_*/activities
	LEFT JOIN /*_PREFIX_*/participations USING (participation_id)
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/activities.activity_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.identifier, activity_date';
