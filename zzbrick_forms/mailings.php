<?php 

/**
 * activities module
 * form script: mailings
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014, 2016-2017, 2019-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include('mailings');
$zz['where']['event_id'] = $brick['data']['event_id'];

$sql = 'SELECT participation_id
		, contacts.identifier
		, contacts.contact
		, (SELECT identification FROM contactdetails
			WHERE contactdetails.contact_id = contacts.contact_id
			AND provider_category_id = %d
			LIMIT 1
		) AS e_mail
		, usergroup
		, participations.contact_id
	FROM participations
	LEFT JOIN persons USING (contact_id)
	LEFT JOIN contacts USING (contact_id)
	LEFT JOIN usergroups USING (usergroup_id)
	WHERE participations.event_id = %d
	AND status_category_id IN (%d, %d)
	ORDER BY usergroup, last_name, first_name';
$sql = sprintf($sql
	, wrap_category_id('participation-status/verified')
	, wrap_category_id('participation-status/participant')
	, wrap_category_id('provider/e-mail')
	, $brick['data']['event_id']
);

$zz['fields'][5]['form_display'] = 'set';
$zz['fields'][5]['fields'][3]['sql'] = $sql;
$zz['fields'][5]['fields'][3]['concat_fields'] = '';
if ($path = wrap_path('contacts_profile[person]', '%s')) {
	$zz['fields'][5]['fields'][3]['concat_0'] = sprintf('<a href="%s" target="_new">', $path);
	$zz['fields'][5]['fields'][3]['concat_1'] = '%s</a>';
} else {
	$zz['fields'][5]['fields'][3]['concat_0'] = '<strong>';
	$zz['fields'][5]['fields'][3]['concat_1'] = '%s</strong>';
}
$zz['fields'][5]['fields'][3]['concat_2'] = ' &middot; %s';
$zz['fields'][5]['fields'][3]['group'] = 'usergroup';
$zz['fields'][5]['fields'][3]['sql_replace']['participation_id'] = 'contact_id';

$zz['hooks']['before_upload'] = 'mf_activities_hook_mailing_add_addresses';

$zz_conf['referer'] = '../';
