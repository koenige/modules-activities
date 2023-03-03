<?php 

/**
 * activities module
 * output of one organisation with links to usergroups or participations
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_userorganisation($params) {
	$sql = 'SELECT DISTINCT contact_id
		FROM participations_contacts
		UNION SELECT DISTINCT organisation_contact_id AS contact_id
		FROM usergroups';
	$contact_ids = wrap_db_fetch($sql, '_dummy_', 'single value');

	$sql = 'SELECT contact_id, contact, identifier, description
		FROM contacts
		WHERE contact_id IN (%s)
		AND identifier = "%s"
		ORDER BY contact';
	$sql = sprintf($sql
		, implode(',', $contact_ids)
		, wrap_db_escape($params[0])
	);
	$data = wrap_db_fetch($sql);
	if (!$data) return false;
	
	$page['title'] = $data['contact'];
	$page['text'] = wrap_template('userorganisation', $data);
	return $page;
}
