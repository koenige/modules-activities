<?php 

/**
 * activities module
 * output of all organisations with links to usergroups or participations
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_userorganisations() {
	$sql = 'SELECT DISTINCT contact_id
		FROM participations_contacts
		UNION SELECT DISTINCT organisation_contact_id AS contact_id
		FROM usergroups';
	$contact_ids = wrap_db_fetch($sql, '_dummy_', 'single value');

	$sql = 'SELECT contact_id, contact, identifier
		FROM contacts
		WHERE contact_id IN (%s)
		ORDER BY contact';
	$sql = sprintf($sql, implode(',', $contact_ids));
	$data = wrap_db_fetch($sql, 'contact_id');
	$page['text'] = wrap_template('userorganisations', $data);
	return $page;
}
