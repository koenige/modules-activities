<?php 

/**
 * activities module
 * table script: participations/websites
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Participations in Websites';
$zz['table'] = '/*_PREFIX_*/participations_websites';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'participation_website_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'participation_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT participation_id, contact, usergroup
	FROM participations
	LEFT JOIN contacts USING (contact_id)
	LEFT JOIN usergroups USING (usergroup_id)
	ORDER BY contacts.identifier, usergroups.identifier';
$zz['fields'][2]['display_field'] = 'participation';

$zz['fields'][3]['field_name'] = 'website_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT website_id, website, domain
	FROM websites
	ORDER BY domain';
$zz['fields'][3]['display_field'] = 'domain';

$zz['subselect']['sql'] = 'SELECT participation_id, domain
	FROM /*_PREFIX_*/websites
	LEFT JOIN /*_PREFIX_*/participations_websites USING (website_id)
';

$zz['sql'] = 'SELECT /*_PREFIX_*/participations_websites.*
		, CONCAT(contact, ", ", usergroup) AS participation
		, domain
	FROM /*_PREFIX_*/participations_websites
	LEFT JOIN /*_PREFIX_*/participations USING (participation_id)
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/usergroups USING (usergroup_id)
	LEFT JOIN /*_PREFIX_*/websites USING (website_id)
';
$zz['sqlorder'] = ' ORDER BY domain, usergroups.identifier, contacts.identifier';
