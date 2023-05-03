<?php 

/**
 * activities module
 * form script: registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (!$brick['data']['published']) wrap_quit(404);
if ($brick['data']['formtemplates_authentication_missing']) wrap_quit(503, wrap_text('Authentication mail is missing.'));
if ($brick['data']['formtemplates_confirmation_missing']) wrap_quit(503, wrap_text('Confirmation mail is missing.'));
if (empty($brick['data']['formfields'])) wrap_quit(503, wrap_text('One or more of the required form fields are missing.'));

$zz = zzform_include('contacts');

$zz['title'] = $brick['data']['event'];
$zz['access'] = 'add_only';
$zz['hooks']['after_insert'] = 'mf_activities_formkit_hook';
$zz['page']['data'] = $brick['data'];

// abstract
// description

wrap_include_files('zzform/formkit');
$zz = mf_activities_formkit($zz, $brick['data']['event_id'], $brick['data']['form_parameters']);

$zz_conf['text'][wrap_setting('lang')]['Add a record'] = $brick['data']['form_parameters']['legend'] ?? $brick['data']['category'];
if (!empty($brick['data']['form_parameters']['action']))
	$zz_conf['text'][wrap_setting('lang')]['Add record'] = $brick['data']['form_parameters']['action'];
