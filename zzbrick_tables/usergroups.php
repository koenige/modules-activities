<?php 

/**
 * Zugzwang Project
 * Table with usergroups
 *
 * http://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Groups';
$zz['table'] = '/*_PREFIX_*/usergroups';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'usergroup_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Group';
$zz['fields'][2]['field_name'] = 'usergroup';
$zz['fields'][2]['type'] = 'text';
$zz['fields'][2]['link'] = [
	'function' => 'mf_activities_group_path',
	'fields' => ['identifier', 'category_parameters']
];

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
$zz['fields'][6]['type'] = 'memo';

$zz['fields'][7]['field_name'] = 'sequence';
$zz['fields'][7]['type'] = 'sequence';
$zz['fields'][7]['hide_in_list'] = true;

$zz['fields'][8]['title'] = wrap_text('Active?');
$zz['fields'][8]['field_name'] = 'active';
$zz['fields'][8]['type'] = 'select';
$zz['fields'][8]['enum'] = ['yes', 'no'];
$zz['fields'][8]['default'] = 'yes';

$zz['fields'][9]['field_name'] = 'parameters';
$zz['fields'][9]['type'] = 'parameter';
$zz['fields'][9]['hide_in_list'] = true;
if (!wrap_access('activities_usergroups_parameters')) {
	$zz['fields'][9]['hide_in_form'] = true;
}

$zz['fields'][10]['title_tab'] = 'M.';
$zz['fields'][10]['title'] = 'Members';
$zz['fields'][10]['field_name'] = 'active_users';
$zz['fields'][10]['type'] = 'display';
$zz['fields'][10]['hide_in_form'] = true;
$zz['fields'][10]['hide_in_list_if_empty'] = true;
$zz['fields'][10]['exclude_from_search'] = true;
$zz['fields'][10]['class'] = 'number';

$zz['fields'][11]['title_tab'] = 'E. M.';
$zz['fields'][11]['title'] = 'Ex-members';
$zz['fields'][11]['field_name'] = 'inactive_users';
$zz['fields'][11]['type'] = 'display';
$zz['fields'][11]['hide_in_form'] = true;
$zz['fields'][11]['hide_in_list_if_empty'] = true;
$zz['fields'][11]['exclude_from_search'] = true;
$zz['fields'][11]['class'] = 'number';

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/usergroups.*, category
		, /*_PREFIX_*/categories.parameters AS category_parameters
		, (SELECT COUNT(*) FROM /*_PREFIX_*/participations
			WHERE /*_PREFIX_*/participations.usergroup_id = /*_PREFIX_*/usergroups.usergroup_id
			AND ISNULL(participations.date_end) OR participations.date_end > CURRENT_DATE()
		) AS active_users
		, (SELECT COUNT(*) FROM /*_PREFIX_*/participations
			WHERE /*_PREFIX_*/participations.usergroup_id = /*_PREFIX_*/usergroups.usergroup_id
			AND participations.date_end <= CURRENT_DATE()
		) AS inactive_users
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
