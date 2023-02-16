<?php 

/**
 * activities module
 * table script: form templates, default texts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2019, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Form Templates: Default Texts';
$zz['table'] = 'formtemplates_default';

$zz['fields'][1]['field_name'] = 'formtemplate_default_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][5]['title'] = 'Form';
$zz['fields'][5]['field_name'] = 'form_category_id';
$zz['fields'][5]['key_field_name'] = 'category_id';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('forms')
);
$zz['fields'][5]['show_values_as_list'] = true;
$zz['fields'][5]['default'] = wrap_category_id('forms/application');
$zz['fields'][5]['display_field'] = 'formcategory';
$zz['fields'][5]['search'] = 'categories.category';
$zz['fields'][5]['sql_translate'] = ['category_id' => 'categories'];

$zz['fields'][4]['title'] = 'Template';
$zz['fields'][4]['field_name'] = 'template_category_id';
$zz['fields'][4]['key_field_name'] = 'category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('template-types')
);
$zz['fields'][4]['show_values_as_list'] = true;
$zz['fields'][4]['display_field'] = 'templatecategory';
$zz['fields'][4]['search'] = 'templatecategories.category';
$zz['fields'][4]['sql_translate'] = ['category_id' => 'categories'];

$zz['fields'][3]['field_name'] = 'language_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT language_id, language_de, variation
	FROM /*_PREFIX_*/languages
	WHERE website = "yes"
	ORDER BY language_de';
$zz['fields'][3]['display_field'] = 'language_de';
$zz['fields'][3]['search'] = '/*_PREFIX_*/languages.language_de';
$zz['fields'][3]['show_values_as_list'] = true;

$zz['fields'][6]['title'] = 'Organisation';
$zz['fields'][6]['field_name'] = 'org_contact_id';
$zz['fields'][6]['type'] = 'select';
$zz['fields'][6]['sql'] = sprintf('SELECT contact_id, contact
	FROM contacts
	WHERE contact_category_id = %d
	ORDER BY contact', wrap_category_id('contact/organisation'));
$zz['fields'][6]['search'] = 'contacts.contact';
$zz['fields'][6]['display_field'] = 'contact';
$zz['fields'][6]['show_values_as_list'] = true;

$zz['fields'][2]['title'] = 'Default Template';
$zz['fields'][2]['field_name'] = 'template_default';
$zz['fields'][2]['type'] = 'memo';
$zz['fields'][2]['hide_in_list'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT formtemplates_default.*
		, categories.category AS formcategory
		, templatecategories.category AS templatecategory
		, CONCAT(languages.language_de, IFNULL(CONCAT(" | ", languages.variation), "")) AS language_de
		, contacts.contact
	FROM formtemplates_default
	LEFT JOIN categories
		ON formtemplates_default.form_category_id = categories.category_id
	LEFT JOIN categories templatecategories
		ON formtemplates_default.template_category_id = templatecategories.category_id
	LEFT JOIN languages USING (language_id)
	LEFT JOIN contacts
		ON formtemplates_default.org_contact_id = contacts.contact_id
';
$zz['sqlorder'] = ' ORDER BY contact, formcategory, templatecategory, language_de, languages.variation';

if (empty($_GET['order']) OR $_GET['order'] === 'contact')
	$zz['list']['group'] = 'org_contact_id';

$zz_conf['copy'] = true;
