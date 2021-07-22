<?php 

/**
 * activities module
 * table script: form fields
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Form Fields';
$zz['table'] = 'formfields';

$zz['fields'][1]['field_name'] = 'formfield_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'form_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['type_detail'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT form_id
		, CONCAT(event, " ", IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
	FROM forms
	LEFT JOIN events USING (event_id)
	ORDER BY events.identifier';
$zz['fields'][2]['sql_ignore'] = ['event'];
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['display_field'] = 'form';

$zz['fields'][7]['title_tab'] = 'Seq.';
$zz['fields'][7]['field_name'] = 'sequence';
$zz['fields'][7]['type'] = 'number';
$zz['fields'][7]['auto_value'] = 'increment';

$zz['fields'][3]['field_name'] = 'formfield';
$zz['fields'][3]['explanation'] = 'The title of the form field that appears on the left side (= what to enter here).';

$zz['fields'][5]['title'] = 'Type';
$zz['fields'][5]['field_name'] = 'formfield_category_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d
	ORDER BY sequence',
	wrap_category_id('field-types')
);
$zz['fields'][5]['display_field'] = 'category';
$zz['fields'][5]['sql_translate'] = ['category_id' => 'categories'];

$zz['fields'][14] = []; // formfields
$zz['fields'][15] = []; // formfields

$zz['fields'][6]['field_name'] = 'area';
$zz['fields'][6]['explanation'] = 'A heading that can be used to divide a long form into parts. Fields with the identical area text will be grouped.';

$zz['fields'][4]['field_name'] = 'explanation';
$zz['fields'][4]['explanation'] = 'An explanation below the field like this one.';
$zz['fields'][4]['list_append_next'] = true;
$zz['fields'][4]['list_suffix'] = '<br>';
$zz['fields'][4]['type'] = 'memo';
$zz['fields'][4]['rows'] = 2;

$zz['fields'][8]['field_name'] = 'parameters';
$zz['fields'][8]['type'] = 'parameter';
$zz['fields'][8]['list_append_show_title'] = true;

$zz['fields'][11]['title'] = 'Editable from';
$zz['fields'][11]['field_name'] = 'edit_from';
$zz['fields'][11]['type'] = 'datetime';
$zz['fields'][11]['hide_in_form'] = true;
$zz['fields'][11]['hide_in_list'] = true;

$zz['fields'][12]['title'] = 'Editable by';
$zz['fields'][12]['field_name'] = 'edit_by';
$zz['fields'][12]['type'] = 'datetime';
$zz['fields'][12]['hide_in_form'] = true;
$zz['fields'][12]['hide_in_list'] = true;

$zz['fields'][13]['field_name'] = 'main_formfield_id';
$zz['fields'][13]['type'] = 'select';
$zz['fields'][13]['hide_in_form'] = true;
$zz['fields'][13]['hide_in_list'] = true;

$zz['sql'] = 'SELECT formfields.*
		, categories.category_id, categories.category
		, events.event
	FROM formfields
	LEFT JOIN forms USING (form_id)
	LEFT JOIN events USING (event_id)
	LEFT JOIN categories
		ON formfields.formfield_category_id = categories.category_id
';
$zz['sqlorder'] = ' ORDER BY event, sequence';
$zz['sql_translate'] = ['category_id' => 'categories'];

$zz['list']['group'] = 'area';

$zz['subtitle']['form_id']['sql'] = $zz['fields'][2]['sql'];
$zz['subtitle']['form_id']['var'] = ['event', 'duration'];
$zz['subtitle']['form_id']['concat'] = ', ';
$zz['subtitle']['form_id']['format'][1] = 'wrap_date';
