<?php 

/**
 * activities module
 * table script: participations of a contact
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['contact_id'])) wrap_quit(404);
$zz = zzform_include_table('participations');

$zz['where']['contact_id'] = $brick['data']['contact_id'];

$zz['fields'][10]['hide_in_list'] = true;
$zz['fields'][10]['hide_in_form'] = true;

$zz['filter'][1]['sql'] = wrap_edit_sql(
	$zz['filter'][1]['sql'], 'WHERE', sprintf('contact_id = %d', $brick['data']['contact_id'])
);

$zz['page']['referer'] = mf_contacts_profile_path(['identifier' => $brick['data']['identifier'], 'contact_parameters' => 'type='.$brick['data']['scope']]);
$zz['page']['breadcrumbs'][]['title'] = wrap_text('Participations');
$zz['page']['dont_show_title_as_breadcrumb'] = true;

$zz['conditions'][10]['scope'] = 'record';
$zz['conditions'][10]['where'] = 'contact_categories.parameters LIKE "%&contacts_no_delete=1%"';

$zz['if'][10]['record']['delete'] = false;
$zz['if'][10]['record']['edit'] = false;
