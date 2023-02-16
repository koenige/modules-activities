<?php 

/**
 * activities module
 * table script: form templates
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Form Templates';
$zz['table'] = 'formtemplates';

$zz['fields'][1]['field_name'] = 'formtemplate_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'form_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['type_detail'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT form_id
		, event, CONCAT(IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
	FROM forms
	LEFT JOIN events USING (event_id)
	ORDER BY events.identifier';
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['display_field'] = 'event';

$zz['fields'][4]['title'] = 'Type';
$zz['fields'][4]['field_name'] = 'template_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
		, IF(parameters LIKE "%%&formfield=1%%", 1, NULL) AS show_formfield
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d
	ORDER BY sequence',
	wrap_category_id('template-types')
);
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['sql_translate'] = ['category_id' => 'categories'];
$zz['fields'][4]['dependent_fields'][5]['if_selected'] = 'show_formfield';
$zz['fields'][4]['sql_ignore'] = ['show_formfield'];

$zz['fields'][3]['field_name'] = 'template';
$zz['fields'][3]['explanation'] = wrap_text('Content of the template.');
$zz['fields'][3]['type'] = 'memo';
$zz['fields'][3]['rows'] = 6;

$zz['fields'][5]['field_name'] = 'formfield_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = 'SELECT formfield_id, formfield FROM formfields ORDER BY sequence';
$zz['fields'][5]['display_field'] = 'formfield';
$zz['fields'][5]['hide_in_list_if_empty'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT formtemplates.*
		, CONCAT(event, " ", IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS event
		, categories.category_id, categories.category
		, formfields.formfield
	FROM formtemplates
	LEFT JOIN forms USING (form_id)
	LEFT JOIN formfields USING (formfield_id)
	LEFT JOIN events USING (event_id)
	LEFT JOIN categories
		ON formtemplates.template_category_id = categories.category_id
';
$zz['sqlorder'] = ' ORDER BY events.identifier, categories.sequence';
$zz['sql_translate'] = ['category_id' => 'categories'];

$zz['subtitle']['form_id']['sql'] = $zz['fields'][2]['sql'];
$zz['subtitle']['form_id']['var'] = ['event', 'duration'];
$zz['subtitle']['form_id']['concat'] = ', ';
$zz['subtitle']['form_id']['format'][1] = 'wrap_date';

$zz['unique_ignore_null'] = true;
