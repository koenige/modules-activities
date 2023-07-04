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


$zz = zzform_include('forms');

$zz['title'] = 'Form';
$zz['where']['event_id'] = $brick['data']['event_id'];
$zz['access'] = 'add_then_edit';

$zz['page']['referer'] = '../';
