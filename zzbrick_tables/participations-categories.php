<?php 

/**
 * activities module
 * Table definition for 'Participations/Categories'
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/actvities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Categories of Participations';
$zz['table'] = 'participations_categories';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'participation_category_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'participation_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT participation_id, contact, identifier
	FROM participations
	LEFT JOIN contacts USING (contact_id)
	ORDER BY contact';
$zz['fields'][2]['display_field'] = 'contact';

$zz['fields'][4]['title'] = 'No.';
$zz['fields'][4]['field_name'] = 'sequence';
$zz['fields'][4]['type'] = 'number';
$zz['fields'][4]['auto_value'] = 'increment';
$zz['fields'][4]['def_val_ignore'] = true;

$zz['fields'][3]['field_name'] = 'category_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['display_field'] = 'category';
//$zz['fields'][3]['add_details'] = 'categories';
$zz['fields'][3]['id_field_name'] = 'categories.category_id';
$zz['fields'][3]['sql'] = sprintf('SELECT categories.category_id, categories.category
		, IF(main.category_id != %d, main.category, "") AS main_category, categories.main_category_id
	FROM categories
	LEFT JOIN categories main
		ON main.category_id = categories.main_category_id
	ORDER BY main.sequence, categories.sequence, categories.category', 
	wrap_category_id('participations')
);
$zz['fields'][3]['show_hierarchy'] = 'main_category_id';
$zz['fields'][3]['show_hierarchy_subtree'] = wrap_category_id('participations');

$zz['fields'][5]['field_name'] = 'type_category_id';
$zz['fields'][5]['type'] = 'hidden';
$zz['fields'][5]['type_detail'] = 'select';
$zz['fields'][5]['value'] = wrap_category_id('events');
$zz['fields'][5]['hide_in_form'] = true;
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['exclude_from_search'] = true;
$zz['fields'][5]['for_action_ignore'] = true;

$zz['fields'][6]['field_name'] = 'property';
$zz['fields'][6]['typo_cleanup'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT participations_categories.*
		, contact
		, category
	FROM participations_categories
	LEFT JOIN participations USING (participation_id)
	LEFT JOIN categories USING (category_id)
	LEFT JOIN contacts USING (contact_id)
';
$zz['sqlorder'] = ' ORDER BY category, contact, identifier DESC';

$zz['subselect']['sql'] = 'SELECT participation_id, participation_category_id
		, category_id, category, property
	FROM participations_categories
	LEFT JOIN categories USING (category_id)
';
$zz['subselect']['sql_translate'] = ['category_id' => 'categories', 'participation_category_id' => 'participations_categories'];
$zz['subselect']['sql_ignore'] = ['participation_category_id', 'category_id'];
$zz['subselect']['concat_fields'] = ' ';
$zz['subselect']['concat_rows'] = ', ';
$zz['unless']['export_mode']['subselect']['prefix'] = '<br><em>'.wrap_text('Category').': ';
$zz['unless']['export_mode']['subselect']['suffix'] = '</em>';
$zz['export_no_html'] = true;
