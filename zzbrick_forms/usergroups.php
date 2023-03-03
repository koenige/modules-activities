<?php 

/**
 * activities module
 * form script: usergroups
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include_table('usergroups');
$zz['title'] = wrap_text($zz['title']).':<br>'.$brick['data']['contact'];

if (wrap_category_id('relations/organisation')) {
	$zz['sql'] = wrap_edit_sql($zz['sql'], 'JOIN', 
		sprintf('LEFT JOIN /*_PREFIX_*/usergroups_categories
			ON /*_PREFIX_*/usergroups_categories.usergroup_id = /*_PREFIX_*/usergroups.usergroup_id
			AND /*_PREFIX_*/usergroups_categories.category_id = %d'
		, wrap_category_id('relations/organisation')
		)
	);
	$zz['filter'][1]['sql'] = wrap_edit_sql($zz['filter'][1]['sql'], 'JOIN', 
		sprintf('LEFT JOIN /*_PREFIX_*/usergroups_categories
			ON /*_PREFIX_*/usergroups_categories.usergroup_id = /*_PREFIX_*/usergroups.usergroup_id
			AND /*_PREFIX_*/usergroups_categories.category_id = %d'
		, wrap_category_id('relations/organisation')
		)
	);
	$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE', sprintf(
		'(/*_PREFIX_*/usergroups.organisation_contact_id = %d OR NOT ISNULL(/*_PREFIX_*/usergroups_categories.uc_id))'
		, $brick['data']['contact_id'], 
	));
	$zz['filter'][1]['sql'] = wrap_edit_sql($zz['filter'][1]['sql'], 'WHERE', sprintf(
		'(/*_PREFIX_*/usergroups.organisation_contact_id = %d OR NOT ISNULL(/*_PREFIX_*/usergroups_categories.uc_id))'
		, $brick['data']['contact_id'], 
	));
} else {
	$zz['where']['organisation_contact_id'] = $brick['data']['contact_id'];
	$zz['filter'][1]['sql'] = wrap_edit_sql($zz['filter'][1]['sql'], 'WHERE', sprintf('organisation_contact_id = %d', $brick['data']['contact_id']));
}

$zz['fields'][2]['link'] = [
	'field1' => 'identifier',
	'string1' => '/'
];


$zz['fields'][12]['hide_in_list'] = true;
$zz['fields'][12]['hide_in_form'] = true;
$zz['fields'][12]['type'] = 'write_once';
$zz['fields'][12]['type_detail'] = 'hidden';
$zz['fields'][12]['default'] = $brick['data']['contact_id'];

$zz['conditions'][10]['scope'] = 'record';
$zz['conditions'][10]['where'] = 'ISNULL(organisation_contact_id)';

$zz_conf['if'][10]['edit'] = false;
$zz_conf['if'][10]['delete'] = false;
