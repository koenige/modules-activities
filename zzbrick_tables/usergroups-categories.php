<?php 

/**
 * activities module
 * table script: usergroups/categories
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Usergroups/Categories';
$zz['table'] = 'usergroups_categories';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'uc_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'usergroup_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT usergroup_id, usergroup
	FROM usergroups
	ORDER BY usergroup';
$zz['fields'][2]['display_field'] = 'usergroup';

$zz['fields'][3]['field_name'] = 'category_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT category_id, category, description, main_category_id
	FROM categories
	ORDER BY sequence, category';
$zz['fields'][3]['display_field'] = 'category';
$zz['fields'][3]['search'] = 'categories.category';
$zz['fields'][3]['show_hierarchy'] = 'main_category_id';

$zz['fields'][4]['field_name'] = 'last_update';
$zz['fields'][4]['type'] = 'timestamp';
$zz['fields'][4]['hide_in_list'] = true;

$zz['sql'] = 'SELECT usergroups_categories.*
		, usergroups.usergroup
		, categories.category
	FROM usergroups_categories
	LEFT JOIN usergroups USING (usergroup_id)
	LEFT JOIN categories USING (category_id)
';
$zz['sqlorder'] = ' ORDER BY usergroup, category';

$zz['subselect']['sql'] = 'SELECT usergroup_id, description, category
	FROM categories
	LEFT JOIN usergroups_categories USING (category_id)';
$zz['subselect']['field_prefix'][0] = '<abbr title="';
$zz['subselect']['field_suffix'][0] = '">';
$zz['subselect']['field_suffix'][1] = '</abbr>';
$zz['subselect']['concat_rows'] = ', ';
