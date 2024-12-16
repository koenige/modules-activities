<?php 

/**
 * activities module
 * table script: forms
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2019, 2021, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Forms';
$zz['table'] = 'forms';

$zz['fields'][1]['field_name'] = 'form_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT event_id
	, CONCAT(events.event, " (", DATE_FORMAT(events.date_begin, "%s"), ")") AS event 
	FROM events
	WHERE ISNULL(main_event_id)
	ORDER BY date_begin DESC', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = sprintf('CONCAT(events.event, " (", 
	DATE_FORMAT(events.date_begin, "%s"), ")")', wrap_placeholder('mysql_date_format'));
$zz['fields'][2]['unique'] = true;
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;

$zz['fields'][12]['title'] = 'Category';
$zz['fields'][12]['title_tab'] = 'C.';
$zz['fields'][12]['field_name'] = 'form_category_id';
$zz['fields'][12]['type'] = 'select';
$zz['fields'][12]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = /*_ID categories forms _*/';
$zz['fields'][12]['show_values_as_list'] = true;
$zz['fields'][12]['default'] = wrap_category_id('forms/registration');
$zz['fields'][12]['display_field'] = 'category';
$zz['fields'][12]['sql_translate'] = ['category_id' => 'categories'];
$zz['fields'][12]['explanation'] = 'For the labeling of forms as well as the content of default mails';

$zz['fields'][11]['title'] = 'Access';
$zz['fields'][11]['title_tab'] = 'A.';
$zz['fields'][11]['field_name'] = 'access';
$zz['fields'][11]['type'] = 'select';
$zz['fields'][11]['enum'] = ['public', 'login'];
$zz['fields'][11]['enum_title'] = ['P', 'L'];
$zz['fields'][11]['enum_abbr'] = [wrap_text('Public'), wrap_text('Behind login mask')];
$zz['fields'][11]['default'] = 'public';
$zz['fields'][11]['dependent_fields'][10]['if_selected'] = 'login';

$zz['fields'][24]['title'] = 'Salutation';
$zz['fields'][24]['field_name'] = 'address';
$zz['fields'][24]['type'] = 'select';
$zz['fields'][24]['enum'] = ['formal', 'informal'];
$zz['fields'][24]['default'] = 'formal';
$zz['fields'][24]['hide_in_list'] = true;
$zz['fields'][24]['explanation'] = 'Whether to address formally or informally in mails and on website';

$zz['fields'][3]['field_name'] = 'created';
$zz['fields'][3]['type'] = 'write_once';
$zz['fields'][3]['class'] = 'hidden';
$zz['fields'][3]['type_detail'] = 'datetime';
$zz['fields'][3]['default'] = date('Y-m-d H:i:s');
$zz['fields'][3]['dont_copy'] = true;

$zz['fields'][6]['field_name'] = 'header';
$zz['fields'][6]['explanation'] = 'Text to appear before the form';
$zz['fields'][6]['type'] = 'memo';
$zz['fields'][6]['format'] = 'markdown';
$zz['fields'][6]['hide_in_list'] = true;

$zz['fields'][10]['field_name'] = 'lead';
$zz['fields'][10]['explanation'] = 'Text to appear before the form, after header';
$zz['fields'][10]['type'] = 'memo';
$zz['fields'][10]['format'] = 'markdown';
$zz['fields'][10]['rows'] = 3;
$zz['fields'][10]['hide_in_list'] = true;

$zz['fields'][7]['field_name'] = 'footer';
$zz['fields'][7]['explanation'] = 'Text to appear behind the form';
$zz['fields'][7]['type'] = 'memo';
$zz['fields'][7]['format'] = 'markdown';
$zz['fields'][7]['hide_in_list'] = true;

$zz['fields'][5]['field_name'] = 'copy_formfields';
$zz['fields'][5]['type'] = 'option';
$zz['fields'][5]['type_detail'] = 'text';
$zz['fields'][5]['default'] = (!empty($_GET['add']) AND intval($_GET['add']).'' === $_GET['add']) ? $_GET['add'] : 'none';
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['class'] = 'hidden';

$zz['sql'] = 'SELECT forms.*
		, SUBSTRING(categories.category, 1, 1) AS category
		, events.event, events.identifier AS event_identifier
	FROM forms
	LEFT JOIN categories
		ON forms.form_category_id = categories.category_id
	LEFT JOIN events USING (event_id)
';
$zz['sqlorder'] = ' ORDER BY events.identifier';

$zz['hooks']['after_insert'][] = 'mf_activities_copy_formfields';

$zz['record']['copy'] = true;
$zz['if'][1]['record']['delete'] = false;
