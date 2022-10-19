<?php 

/**
 * activities module
 * table script: participations/contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Participations in Organisations';
$zz['table'] = '/*_PREFIX_*/participations_contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'participation_contact_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'participation_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT participation_id, contact, usergroup
	FROM /*_PREFIX_*/participations
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
	ORDER BY /*_PREFIX_*/contacts.identifier, /*_PREFIX_*/usergroups.identifier';
$zz['fields'][2]['display_field'] = 'participation';

$zz['fields'][3]['title'] = 'Organisation';
$zz['fields'][3]['field_name'] = 'contact_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][3]['display_field'] = 'contact';
$zz['fields'][3]['search'] = '/*_PREFIX_*/contacts.contact';

$zz['subselect']['sql'] = 'SELECT participation_id, contact
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/participations_contacts USING (contact_id)
';

$zz['sql'] = 'SELECT /*_PREFIX_*/participations_contacts.*
		, CONCAT(/*_PREFIX_*/contacts.contact, ", ", usergroup) AS participation
		, organisations.contact
	FROM /*_PREFIX_*/participations_contacts
	LEFT JOIN /*_PREFIX_*/contacts organisations USING (contact_id)
	LEFT JOIN /*_PREFIX_*/participations USING (participation_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
	LEFT JOIN /*_PREFIX_*/contacts
		ON /*_PREFIX_*/participations.contact_id = /*_PREFIX_*/contacts.contact_id
';
$zz['sqlorder'] = ' ORDER BY organisations.identifier, /*_PREFIX_*/usergroups.identifier, /*_PREFIX_*/contacts.identifier';
