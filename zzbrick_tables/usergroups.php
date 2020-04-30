<?php 

/**
 * Zugzwang Project
 * Table with usergroups
 *
 * http://www.zugzwang.org/modules/registrations
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Groups';
$zz['table'] = '/*_PREFIX_*/usergroups';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'usergroup_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'usergroup';
$zz['fields'][2]['type'] = 'text';

$zz['fields'][3]['field_name'] = 'usergroup';

$zz['fields'][4]['field_name'] = 'identifier';
$zz['fields'][4]['type'] = 'identifier';
$zz['fields'][4]['fields'] = ['usergroup'];
$zz['fields'][4]['conf_identifier']['exists'] = '-';
$zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['unique'] = true;
$zz['fields'][4]['character_set'] = 'latin1';

$zz['fields'][5]['title'] = 'Category';
$zz['fields'][5]['field_name'] = 'usergroup_category_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('usergroups')
);
$zz['fields'][5]['key_field_name'] = 'category_id';
$zz['fields'][5]['if']['where']['hide_in_form'] = true;
$zz['fields'][5]['if']['where']['hide_in_list'] = true;
$zz['fields'][5]['display_field'] = 'category';

$zz['fields'][6]['field_name'] = 'description';
$zz['fields'][6]['hide_in_list'] = true;

$zz['fields'][7]['field_name'] = 'sequence';
$zz['fields'][7]['type'] = 'sequence';

$zz['fields'][8]['field_name'] = 'active';
$zz['fields'][8]['type'] = 'select';
$zz['fields'][8]['enum'] = ['yes', 'no'];
$zz['fields'][8]['default'] = 'yes';

$zz['fields'][9]['field_name'] = 'parameters';
$zz['fields'][9]['type'] = 'parameters';
$zz['fields'][9]['hide_in_list'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/usergroups.*, category
	FROM /*_PREFIX_*/usergroups
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/usergroups.usergroup_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY sequence, identifier';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/usergroups
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/usergroups.usergroup_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY category';
$zz['filter'][1]['title'] = wrap_text('Category');
$zz['filter'][1]['identifier'] = 'category';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'usergroup_category_id';
$zz['filter'][1]['field_name'] = 'usergroup_category_id';
