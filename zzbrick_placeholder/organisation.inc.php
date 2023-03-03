<?php 

/**
 * activities module
 * placeholder script for organisation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_placeholder_organisation($brick) {
	global $zz_page;
	if (empty($brick['placeholder'])) return $brick;
	if (empty($brick['data'])) $brick['data'] = [];
	
	$sql = 'SELECT contact_id, contact, identifier
		FROM contacts
		WHERE identifier = "%s"
	';
	$sql = sprintf($sql
		, wrap_db_escape($brick['placeholder'])
	);
	$contact = wrap_db_fetch($sql);
	if (!$contact) wrap_quit(404);
	$brick['data'] = array_merge($brick['data'], $contact);

	// access
	$zz_page['access'][] = sprintf('organisation_contact_id:%d', $brick['data']['contact_id']);
	wrap_access_page($zz_page['db']['parameters'] ?? '', $zz_page['access']);

	// breadcrumbs
	$zz_page['breadcrumb_placeholder'][] = [
		'title' => $brick['data']['contact'],
		'url_path' => $brick['data']['identifier']
	];
	
	return $brick;
}
