<?php 

/**
 * activities module
 * form script: registrations, based on events
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (!$brick['data']['form_id']) wrap_quit(404);

$zz = zzform_include_table('formfields');
$zz['where']['form_id'] = $brick['data']['form_id'];

$zz['explanation'] = wrap_text('Here you can define a form field by field.');
$required_fields = mf_activities_formfields_required($brick['data']);
if ($required_fields)
	$zz['explanation'] .= ' '.wrap_text('Note: Each form must have at least these fields:').' '.implode(', ', $required_fields['text']);
$zz['explanation'] = markdown($zz['explanation']);

$zz_conf['referer'] = '../';
