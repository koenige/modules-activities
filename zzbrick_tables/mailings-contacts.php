<?php 

/**
 * activities module
 * table script: recipients of mailings
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2012, 2019-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Mailings: Recipients';
$zz['table'] = 'mailings_contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'mailing_contact_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'mailing_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT mailing_id
	, CONCAT(events.identifier, ": ", IFNULL(sent, CONCAT("ID: ", mailing_id))) AS mail
	FROM mailings
	LEFT JOIN events USING (event_id)
	ORDER BY events.identifier, sent, mailings.last_update';
$zz['fields'][2]['display_field'] = 'mail';
$zz['fields'][2]['search'] = 'CONCAT(events.identifier, ": ", IFNULL(sent, CONCAT("ID: ", mailing_id)))';

$zz['fields'][3]['title'] = 'Recipient';
$zz['fields'][3]['field_name'] = 'recipient_contact_id';
$zz['fields'][3]['key_field_name'] = 'contact_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = sprintf('SELECT contact_id, contact
	, (SELECT identification FROM contactdetails
			WHERE contactdetails.contact_id = contacts.contact_id
			AND provider_category_id = %d
			LIMIT 1
		) AS e_mail
	FROM /*_PREFIX_*/persons
	LEFT JOIN contacts USING (contact_id)
	ORDER BY last_name, first_name', wrap_category_id('provider/e-mail'));
$zz['fields'][3]['display_field'] = 'contact';

$zz['fields'][4]['title'] = 'E-mail';
$zz['fields'][4]['field_name'] = 'recipient_mail';
$zz['fields'][4]['type'] = 'hidden';

$zz['sql'] = 'SELECT mailings_contacts.*
		, CONCAT(events.identifier, ": ", IFNULL(sent, CONCAT("ID: ", mailing_id))) AS mail
		, contact
	FROM mailings_contacts
	LEFT JOIN mailings USING (mailing_id)
	LEFT JOIN events USING (event_id)
	LEFT JOIN contacts
		ON contacts.contact_id = mailings_contacts.recipient_contact_id
';
$zz['sqlorder'] = ' ORDER BY events.identifier, sent, mailings.last_update, contacts.identifier';
