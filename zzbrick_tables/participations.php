<?php 

/**
 * activities module
 * table script: participations
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Participations';
$zz['table'] = '/*_PREFIX_*/participations';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'participation_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['type_detail'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM contacts ORDER BY contact';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['list_append_next'] = true;
$zz['fields'][2]['link'] = [
	'function' => 'mf_contacts_profile_path',
	'fields' => ['identifier', 'contact_parameters']
];
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['if']['where']['list_append_next'] = false;

$zz['fields'][10]['title'] = 'E-Mail';
$zz['fields'][10]['field_name'] = 'e_mail';
$zz['fields'][10]['type'] = 'display';
$zz['fields'][10]['type_detail'] = 'mail';
$zz['fields'][10]['list_prefix'] = '<br>';
$zz['fields'][10]['search'] = sprintf('(SELECT identification FROM /*_PREFIX_*/contactdetails
			WHERE /*_PREFIX_*/contactdetails.contact_id = /*_PREFIX_*/participations.contact_id
			AND provider_category_id = %d LIMIT 1)', wrap_category_id('provider/e-mail'));
$zz['fields'][10]['if']['add']['hide_in_form'] = true;

$zz['fields'][3]['field_name'] = 'usergroup_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT usergroup_id, usergroup, category
		, IF(categories.parameters LIKE "%&hide[date_begin]=1%"
			, IF(usergroups.parameters LIKE "%&show[date_begin]%", 1, NULL), 1
		) AS show_date_begin
		, IF(categories.parameters LIKE "%&hide[date_end]=1%"
			, IF(usergroups.parameters LIKE "%&show[date_end]%", 1, NULL), 1
		) AS show_date_end
		, IF(categories.parameters LIKE "%&hide[status_category_id]=1%"
			, IF(usergroups.parameters LIKE "%&show[status_category_id]%", 1, NULL), 1
		) AS show_status_category_id
		, IF(categories.parameters LIKE "%&hide[sequence]=1%"
			, IF(usergroups.parameters LIKE "%&show[sequence]%", 1, NULL), 1
		) AS show_sequence
		, IF(categories.parameters LIKE "%&hide[role]=1%"
			, IF(usergroups.parameters LIKE "%&show[role]%", 1, NULL), 1
		) AS show_role
		, categories.parameters
	FROM usergroups
	LEFT JOIN categories
		ON usergroups.usergroup_category_id = categories.category_id
	WHERE (ISNULL(categories.parameters) OR categories.parameters NOT LIKE "%no_participations=1%")
	ORDER BY category, identifier';
$zz['fields'][3]['sql_ignore'] = [
	'show_date_begin', 'show_date_end', 'show_status_category_id', 'parameters',
	'show_sequence', 'show_role'
];
$zz['fields'][3]['display_field'] = 'usergroup';
$zz['fields'][3]['group'] = 'category';
$zz['fields'][3]['if']['where']['hide_in_form'] = true;
$zz['fields'][3]['if']['where']['hide_in_list'] = true;
$zz['fields'][3]['dependent_fields'][4]['if_selected'] = 'show_date_begin';
$zz['fields'][3]['dependent_fields'][5]['if_selected'] = 'show_date_end';
$zz['fields'][3]['dependent_fields'][6]['if_selected'] = 'show_status_category_id';
$zz['fields'][3]['dependent_fields'][6]['value'] = 'parameters';
$zz['fields'][3]['dependent_fields'][9]['if_selected'] = 'show_sequence';
$zz['fields'][3]['dependent_fields'][11]['if_selected'] = 'show_role';

$zz['fields'][4]['field_name'] = 'date_begin';
$zz['fields'][4]['title_tab'] = 'Begin';
$zz['fields'][4]['type'] = 'date';
$zz['fields'][4]['hide_in_list_if_empty'] = true;

$zz['fields'][5]['field_name'] = 'date_end';
$zz['fields'][5]['title_tab'] = 'End';
$zz['fields'][5]['type'] = 'date';
$zz['fields'][5]['hide_in_list_if_empty'] = true;

$zz['fields'][6]['title'] = 'Category';
$zz['fields'][6]['field_name'] = 'status_category_id';
$zz['fields'][6]['type'] = 'select';
$zz['fields'][6]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('participation-status')
);
$zz['fields'][6]['key_field_name'] = 'category_id';
$zz['fields'][6]['search'] = '/*_PREFIX_*/categories.category';
$zz['fields'][6]['if']['where']['hide_in_form'] = true;
$zz['fields'][6]['if']['where']['hide_in_list'] = true;
$zz['fields'][6]['display_field'] = 'category';
$zz['fields'][6]['hide_in_list_if_empty'] = true;

$zz['fields'][11]['field_name'] = 'role';
$zz['fields'][11]['hide_in_list_if_empty'] = true;
$zz['fields'][11]['sql'] = 'SELECT DISTINCT role, role FROM participations';

$zz['fields'][9]['title_tab'] = 'Seq.';
$zz['fields'][9]['field_name'] = 'sequence';
$zz['fields'][9]['type'] = 'number';
$zz['fields'][9]['hide_in_list_if_empty'] = true;

$zz['fields'][7]['field_name'] = 'remarks';
$zz['fields'][7]['hide_in_list'] = true;
$zz['fields'][7]['type'] = 'memo';
$zz['fields'][7]['rows'] = 3;
$zz['fields'][7]['explanation'] = '(internal remarks only)';

$zz['fields'][8]['title'] = 'Hash';
$zz['fields'][8]['field_name'] = 'verification_hash';
$zz['fields'][8]['type'] = 'hidden';
$zz['fields'][8]['class'] = 'hidden';
$zz['fields'][8]['hide_in_list'] = true;
$zz['fields'][8]['function'] = 'mf_activities_random_hash';
$zz['fields'][8]['fields'] = ['verification_hash'];
$zz['fields'][8]['export'] = false;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = sprintf('SELECT /*_PREFIX_*/participations.*, contact, usergroup
		, IF(/*_PREFIX_*/categories.parameters LIKE "%%&hide_in_list=1%%", "", /*_PREFIX_*/categories.category) AS category
		, (SELECT identification FROM /*_PREFIX_*/contactdetails
			WHERE /*_PREFIX_*/contactdetails.contact_id = /*_PREFIX_*/participations.contact_id
			AND provider_category_id = %d LIMIT 1) AS e_mail
		,  /*_PREFIX_*/contacts.identifier
		, contact_categories.parameters AS contact_parameters
	FROM /*_PREFIX_*/participations
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/persons USING (contact_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/participations.status_category_id = /*_PREFIX_*/categories.category_id
	LEFT JOIN /*_PREFIX_*/categories contact_categories
		ON contact_categories.category_id = /*_PREFIX_*/contacts.contact_category_id
'
	, wrap_category_id('provider/e-mail')
);
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/usergroups.identifier
	, IF(ISNULL(/*_PREFIX_*/participations.sequence), 1, NULL), /*_PREFIX_*/participations.sequence
	, IFNULL(/*_PREFIX_*/persons.last_name, /*_PREFIX_*/contacts.identifier)
	, IFNULL(/*_PREFIX_*/persons.first_name, /*_PREFIX_*/contacts.identifier)
	, /*_PREFIX_*/contacts.identifier
	, date_begin';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/participations
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/participations.status_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY category';
$zz['filter'][1]['title'] = wrap_text('Status');
$zz['filter'][1]['identifier'] = 'status';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'status_category_id';
$zz['filter'][1]['field_name'] = 'status_category_id';

$zz['filter'][2]['title'] = wrap_text('Active?');
$zz['filter'][2]['identifier'] = 'active';
$zz['filter'][2]['type'] = 'list';
$zz['filter'][2]['where'] = 'IF(ISNULL(participations.date_end) 
	OR participations.date_end > CURRENT_DATE(), "1", "2")';
$zz['filter'][2]['selection'][1] = wrap_text('active');
$zz['filter'][2]['selection'][2] = wrap_text('inactive');
$zz['filter'][2]['default_selection'] = 1;

// filter 3: mail address?

$zz['subtitle']['contact_id']['sql'] = $zz['fields'][2]['sql'];
$zz['subtitle']['contact_id']['var'] = ['contact'];
