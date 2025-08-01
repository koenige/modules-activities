<?php 

/**
 * activities module
 * table script: usergroups
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2006-2013, 2016-2017, 2019-2024 Gustaf Mossakowski
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
$zz['fields'][2]['typo_cleanup'] = true;
$zz['fields'][2]['typo_remove_double_spaces'] = true;
$zz['fields'][2]['list_prefix'] = '<strong>';
$zz['fields'][2]['list_suffix'] = '</strong>';
$zz['fields'][2]['if'][1]['list_prefix'] = '<del>';
$zz['fields'][2]['if'][1]['list_suffix'] = '</del>';

$zz['fields'][4]['field_name'] = 'identifier';
$zz['fields'][4]['type'] = 'identifier';
$zz['fields'][4]['fields'] = ['usergroup'];
$zz['fields'][4]['identifier']['exists'] = '-';
$zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['unique'] = true;
$zz['fields'][4]['character_set'] = 'latin1';

$zz['fields'][5]['title'] = 'Category';
$zz['fields'][5]['field_name'] = 'usergroup_category_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = /*_ID categories usergroups _*/';
$zz['fields'][5]['if']['where']['hide_in_form'] = true;
$zz['fields'][5]['if']['where']['hide_in_list'] = true;
$zz['fields'][5]['display_field'] = 'category';
$zz['fields'][5]['if'][1]['list_prefix'] = '<del>';
$zz['fields'][5]['if'][1]['list_suffix'] = '</del>';
if (!empty($_GET['filter']['category']))
	$zz['fields'][5]['hide_in_list'] = true;

$zz['fields'][6]['field_name'] = 'description';
$zz['fields'][6]['type'] = 'memo';
if (wrap_setting('activities_usergroups_show_description')) {
	$zz['fields'][6]['format'] = 'markdown';
	$zz['fields'][6]['list_format'] = 'markdown';
} else {
	$zz['fields'][6]['hide_in_list'] = true;
}

$zz['fields'][7]['field_name'] = 'sequence';
$zz['fields'][7]['type'] = 'text';
$zz['fields'][7]['hide_in_list'] = true;

$zz['fields'][8]['title'] = wrap_text('Active?');
$zz['fields'][8]['field_name'] = 'active';
$zz['fields'][8]['type'] = 'select';
$zz['fields'][8]['enum'] = ['yes', 'no'];
$zz['fields'][8]['default'] = 'yes';
$zz['fields'][8]['hide_in_list'] = true;

if (wrap_access('activities_usergroups_edit')) {
	$sql = 'SELECT category_id, category FROM /*_PREFIX_*/categories
		WHERE parameters LIKE "%&usergroup_category=1%"';
	$categories = wrap_db_fetch($sql, 'category_id');
	$categories = wrap_translate($categories, 'categories');
	$no = 30;
	if ($categories)
		$zz['fields'][6]['list_append_next'] = true;
	
	foreach ($categories as $category) {
		$zz['fields'][$no] = zzform_include('usergroups-categories');
		$zz['fields'][$no]['title'] = $category['category'];
		$zz['fields'][$no]['table_name'] = $zz['fields'][$no]['table'].'_'.$no;
		$zz['fields'][$no]['type'] = 'subtable';
		$zz['fields'][$no]['min_records'] = 1;
		$zz['fields'][$no]['form_display'] = 'set';
		$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
		$zz['fields'][$no]['fields'][3]['show_hierarchy_subtree'] = $category['category_id'];
		$zz['fields'][$no]['sql'] .= sprintf(' WHERE main_category_id = %d', $category['category_id']);
		$zz['fields'][$no]['subselect']['prefix'] = sprintf('<p><em>%s: ', $category['category']);
		$zz['fields'][$no]['subselect']['suffix'] = '</em></p>';
		$zz['fields'][$no]['subselect']['sql'] .= sprintf(' WHERE main_category_id = %d', $category['category_id']);
		if ($no < count($categories) + 30 - 1)
			$zz['fields'][$no]['list_append_next'] = true;
		$no++;
	}
}

if (wrap_setting('activities_usergroups_organisation')) {
	$zz['fields'][12]['title'] = 'Organisation';
	$zz['fields'][12]['field_name'] = 'organisation_contact_id';
	$zz['fields'][12]['type'] = 'select';
	$zz['fields'][12]['sql'] = 'SELECT contact_id, contact
		FROM /*_PREFIX_*/contacts
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
		WHERE /*_PREFIX_*/categories.parameters LIKE "%&organisation=1%"
		ORDER BY contact';
	$zz['fields'][12]['display_field'] = 'contact';
}

$zz['fields'][9]['field_name'] = 'parameters';
$zz['fields'][9]['type'] = 'parameter';
$zz['fields'][9]['hide_in_list'] = true;
if (!wrap_access('activities_usergroups_parameters'))
	$zz['fields'][9]['hide_in_form'] = true;

if (wrap_access('activities_usergroups_edit')) {
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
}

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/usergroups.*, category
		, /*_PREFIX_*/categories.parameters AS category_parameters
		, (SELECT COUNT(*) FROM /*_PREFIX_*/participations
			WHERE /*_PREFIX_*/participations.usergroup_id = /*_PREFIX_*/usergroups.usergroup_id
			AND (ISNULL(participations.date_end) OR participations.date_end > CURRENT_DATE())
		) AS active_users
		, (SELECT COUNT(*) FROM /*_PREFIX_*/participations
			WHERE /*_PREFIX_*/participations.usergroup_id = /*_PREFIX_*/usergroups.usergroup_id
			AND participations.date_end <= CURRENT_DATE()
		) AS inactive_users
		, /*_PREFIX_*/contacts.contact
	FROM /*_PREFIX_*/usergroups
	LEFT JOIN /*_PREFIX_*/contacts
		ON /*_PREFIX_*/usergroups.organisation_contact_id = /*_PREFIX_*/contacts.contact_id
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/usergroups.usergroup_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/categories.sequence, IFNULL(/*_PREFIX_*/usergroups.sequence, 255), usergroup';

if (empty($_GET['filter']['category']) AND (empty($_GET['order']) OR $_GET['order'] === 'category'))
	$zz['list']['group'] = 'category';

$zz['filter'][1]['sql'] = 'SELECT /*_PREFIX_*/categories.category_id, category
	FROM /*_PREFIX_*/usergroups
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/usergroups.usergroup_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY category';
$zz['filter'][1]['title'] = wrap_text('Category');
$zz['filter'][1]['identifier'] = 'category';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'usergroup_category_id';
$zz['filter'][1]['field_name'] = 'usergroup_category_id';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = '/*_PREFIX_*/usergroups.active = "no"';

if (!wrap_access('activities_usergroups_edit'))
	$zz['access'] = 'none';
