<?php 

/**
 * activities module
 * table script: registration texts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2018-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Registration Texts';
$zz['table'] = 'registrationtexts';

$zz['fields'][1]['field_name'] = 'registrationtext_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT contact_id, contact
	FROM contacts
	WHERE contact_category_id = %d
	ORDER BY contact', wrap_category_id('contact/person'));
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['list_append_next'] = true;
$zz['fields'][2]['list_prefix'] = '<strong>';
$zz['fields'][2]['list_suffix'] = '</strong> – ';

$zz['fields'][3]['field_name'] = 'formfield_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT formfield_id
		, CONCAT(event, " ", formfields.sequence)
	FROM formfields
	LEFT JOIN forms USING (form_id)
	LEFT JOIN events USING (event_id)
	ORDER BY identifier, formfields.sequence';
$zz['fields'][3]['display_field'] = 'formfield';
$zz['fields'][3]['search'] = 'CONCAT(event, " ", formfields.sequence)';
$zz['fields'][3]['list_append_next'] = true;
$zz['fields'][3]['list_suffix'] = '<br>';

$zz['fields'][4]['title'] = 'Text';
$zz['fields'][4]['field_name'] = 'registrationtext';
$zz['fields'][4]['type'] = 'memo';


$zz['subselect']['sql'] = 'SELECT contact_id, registrationtext
	FROM registrationtexts
';
$zz['subselect']['concat_rows'] = ', ';
$zz['subselect']['prefix'] = '';
$zz['subselect']['suffix'] = '';

$zz['sql'] = 'SELECT registrationtexts.*
		, contact
		, CONCAT(event, " ", formfields.sequence) AS formfield
	FROM registrationtexts
	LEFT JOIN contacts USING (contact_id)
	LEFT JOIN formfields USING (formfield_id)
	LEFT JOIN forms USING (form_id)
	LEFT JOIN events USING (event_id)
';
$zz['sqlorder'] = ' ORDER BY events.identifier, formfields.sequence';
