<?php 

/**
 * activities module
 * form script: usergroups
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

$zz = zzform_include_table('registrations');
$zz['where']['event_id'] = $brick['data']['event_id'];

$zz['title'] = $brick['data']['event'];

$zz['access'] = 'add_only';

// abstract
// description

wrap_include_files('zzform/formkit');
$zz = mf_activities_formkit($zz, $brick['data']['event_id']);
