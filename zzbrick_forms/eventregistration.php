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


require_once __DIR__.'/eventregistrations.php';

$zz['title'] = 'Registration';
$zz['where']['event_id'] = $brick['data']['event_id'];
$zz['access'] = 'edit_only';

$zz['page']['referer'] = '../';
