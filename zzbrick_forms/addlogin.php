<?php

/**
 * activities module
 * form script: create own login
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


// 0: contact_id
if (count($brick['vars']) !== 1) wrap_quit(403);
if (!is_numeric($brick['vars'][0])) wrap_quit(403);

$zz = zzform_include_table('logins');
// remarks: logins script must work with contact_id

$password_min_length = !empty($zz['fields'][3]['minlength'])
	? $zz['fields'][3]['minlength'] : 8;

$zz['title'] = ' ';
$zz['explanation'] = '<h2>'.wrap_text('Set a Password').'</h2><p>'
	.wrap_text('Please set a password for the login.').' '
	.sprintf(wrap_text('The password must be at least %d characters long.'), $password_min_length).' '
	.wrap_text('In the future, access is granted with the username below and the password you chose.')
	.'</p>';
$zz['access'] = 'add_only';

$zz['where']['contact_id'] = $brick['vars'][0];

// 11 = contact
$zz['fields'][11]['hide_in_form'] = true;

// 2 = username
if (wrap_get_setting('login_with_email')) {
	$sql = sprintf('SELECT identification
		FROM contactdetails
		WHERE contact_id = %d
		AND provider_category_id = %d LIMIT 1', $brick['vars'][0], wrap_category_id('provider/e-mail'));
	$zz['sql'] = wrap_edit_sql($zz['sql'], 'SELECT', sprintf('(%s) AS username', $sql));
} else {
	$sql = sprintf('SELECT identifier
		FROM contacts
		WHERE contact_id = %d', $brick['vars'][0]);
}
$zz['fields'][2]['type'] = 'display';
$zz['fields'][2]['display_value'] = wrap_db_fetch($sql, '', 'single value');

// 6 = login_rights
$zz['fields'][6]['type'] = 'hidden';
$zz['fields'][6]['value'] = 'registration';
$zz['fields'][6]['hide_in_form'] = true;

// 3 = password
$zz['fields'][3]['type'] = 'password_change';
unset($zz['fields'][3]['function']); // no password function, e. g. random pwd
$zz['fields'][3]['dont_require_old_password'] = true;

// 9 = password_change
$zz['fields'][9]['type'] = 'hidden';
$zz['fields'][9]['hide_in_form'] = true;
$zz['fields'][9]['value'] = 'no';

// 13 = generate random password
unset($zz['fields'][13]);

// keep URL from confirmation script
if (!empty($brick['local_settings']['url_self']))
	$zz_conf['url_self'] = $brick['local_settings']['url_self'];
if (!empty($brick['local_settings']['query_strings']))
	$zz['page']['query_strings'] = $brick['local_settings']['query_strings'];

$zz['hooks']['after_insert'] = 'mf_activities_addlogin_password';

$zz_conf['text'][$zz_setting['lang']]['Add a record'] = ' ';
$zz_conf['text'][$zz_setting['lang']]['Add record'] = wrap_text('Save password');
$zz_conf['no_timeframe'] = true;

$zz_conf['redirect']['successful_insert'] = isset($brick['local_settings']['link'])
	? $brick['local_settings']['link'] : $zz_setting['login_entryurl'];


/**
 * log user into database after password is set
 *
 * @param array $ops
 * @return array
 */
function mf_activities_addlogin_password($ops) {
	$login_id = false;
	foreach ($ops['return'] as $index => $table) {
		if ($table['table'] !== 'logins') continue;
		$login_id = $ops['record_new'][$index]['login_id'];
		$username = $ops['record_old'][$index]['username']; // old, is set via display in logins
	}
	if (!$login_id) return [];

	// get user data
	$sql = sprintf(wrap_sql_login(), $username);
	$data = wrap_db_fetch($sql);
	if (!$data) return false;

	// register user data
	$success = wrap_session_start();
	$_SESSION['logged_in'] = true;
	wrap_register($data['user_id'], $data);
	session_write_close();
	return [];
}
