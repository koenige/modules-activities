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


$zz['title'] = 'Default Templates for Forms';
$zz['table'] = 'formtemplates_defaults';

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

$iso_lang = in_array(wrap_setting('lang'), wrap_setting('language_translations')) ? wrap_setting('lang') : reset(wrap_setting('language_translations'));
$zz['fields'][3]['field_name'] = 'language_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = sprintf('SELECT language_id, language_%s, variation
	FROM /*_PREFIX_*/languages
	WHERE website = "yes"
	ORDER BY language_%s', $iso_lang, $iso_lang);
$zz['fields'][3]['display_field'] = 'language_name';
$zz['fields'][3]['search'] = sprintf('/*_PREFIX_*/languages.language_%s', $iso_lang);
$zz['fields'][3]['show_values_as_list'] = true;

$zz['fields'][6]['title'] = 'Organisation';
$zz['fields'][6]['field_name'] = 'org_contact_id';
$zz['fields'][6]['key_field_name'] = 'contact_id';
$zz['fields'][6]['type'] = 'select';
if (wrap_category_id('contact/organisation', 'check'))
	$zz['fields'][6]['sql'] = sprintf('SELECT contact_id, contact
		FROM contacts
		WHERE contact_category_id = %d
		ORDER BY contact', wrap_category_id('contact/organisation'));
else
	$zz['fields'][6]['sql'] = 'SELECT contact_id, contact
		FROM contacts
		ORDER BY contact';
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


$zz['sql'] = sprintf('SELECT formtemplates_defaults.*
		, categories.category_id AS formcategory_id
		, categories.category AS formcategory
		, templatecategories.category_id AS templatecategory_id
		, templatecategories.category AS templatecategory
		, CONCAT(languages.language_%s, IFNULL(CONCAT(" | ", languages.variation), "")) AS language_name
		, contacts.contact
	FROM formtemplates_defaults
	LEFT JOIN categories
		ON formtemplates_defaults.form_category_id = categories.category_id
	LEFT JOIN categories templatecategories
		ON formtemplates_defaults.template_category_id = templatecategories.category_id
	LEFT JOIN languages USING (language_id)
	LEFT JOIN contacts
		ON formtemplates_defaults.org_contact_id = contacts.contact_id
', $iso_lang);
$zz['sqlorder'] = sprintf(' ORDER BY contact, formcategory, templatecategory, language_%s, languages.variation', $iso_lang);

$zz['sql_translate']['formcategory_id'] = ['formcategory' => 'categories.category'];
$zz['sql_translate']['templatecategory_id'] = ['templatecategory' => 'categories.category'];

if (empty($_GET['order']) OR $_GET['order'] === 'contact')
	$zz['list']['group'] = 'org_contact_id';

$zz['subtitle']['org_contact_id']['sql'] = $zz['fields'][6]['sql'];
$zz['subtitle']['org_contact_id']['var'] = ['contact'];

$zz_conf['copy'] = true;

if (!wrap_access('activities_formtemplates_default_edit'))
	$zz['access'] = 'none';
