<?php 

/**
 * activities module
 * form script: default form templates per organisation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (!empty($brick['data']['website_id'])) {
	$sql = 'SELECT contact_id FROM websites WHERE website_id = %d';
	$sql = sprintf($sql, $brick['data']['website_id']);
	$org_contact_id = wrap_db_fetch($sql, '', 'single value');
	if (!$org_contact_id) wrap_quit(404);
} elseif (!empty($brick['data']['org_contact_id'])) {
	$org_contact_id = $brick['data']['org_contact_id'];
} else {
	wrap_quit(404);
}

$zz = zzform_include_table('formtemplates-defaults');

$zz['where']['org_contact_id'] = $org_contact_id;

$zz['fields'][6]['hide_in_form'] = true;
$zz['fields'][6]['hide_in_list'] = true;

unset($zz['list']['group']);

$zz_conf['referer'] = '../';
