<?php 

/**
 * Zugzwang Project
 * Table with participations of a contact
 *
 * http://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


require __DIR__.'/../zzbrick_tables/participations.php';

if (empty($brick['vars'])) wrap_quit(404);

$sql = 'SELECT contact_id, contact, identifier
			, SUBSTRING_INDEX(path, "/", -1) AS scope
	FROM contacts
	LEFT JOIN categories
		ON contacts.contact_category_id = categories.category_id
	WHERE identifier = "%s"';
$sql = sprintf($sql, wrap_db_escape($brick['vars'][0]));
$data = wrap_db_fetch($sql);
if (!$data) wrap_quit(404);

$zz['where']['contact_id'] = $data['contact_id'];

$zz['fields'][10]['hide_in_list'] = true;
$zz['fields'][10]['hide_in_form'] = true;

$zz['filter'][1]['sql'] = wrap_edit_sql(
	$zz['filter'][1]['sql'], 'WHERE', sprintf('contact_id = %d', $data['contact_id'])
);

$zz_conf['referer'] = mf_contacts_profile_path(['identifier' => $data['identifier'], 'contact_parameters' => 'type='.$data['scope']]);
$zz['page']['breadcrumbs'][] = sprintf('<a href="%s">%s</a>'
	, $zz_conf['referer'], $data['contact']
);
$zz['page']['breadcrumbs'][] = wrap_text('Participations');
$zz_conf['dont_show_title_as_breadcrumb'] = true;
