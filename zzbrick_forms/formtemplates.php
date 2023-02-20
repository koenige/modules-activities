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


$zz = zzform_include_table('formtemplates');

$zz['where']['form_id'] = $brick['data']['form_id'];

$zz['fields'][5]['sql'] = wrap_edit_sql($zz['fields'][5]['sql'], 'WHERE', sprintf('form_id = %d', $brick['data']['form_id']));

$zz_conf['referer'] = '../';
