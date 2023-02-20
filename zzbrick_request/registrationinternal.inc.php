<?php 

/**
 * activities module
 * output of a single registration, backend
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_registrationinternal($params, $settings, $data) {
	$page['title'] = $data['event'];
	$page['text'] = wrap_template('registrationinternal', $data);
	return $page;
}
