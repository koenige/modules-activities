<?php 

/**
 * activities module
 * placeholder script for registration form
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_placeholder_form($brick) {
	global $zz_page;
	if (empty($brick['placeholder'])) return $brick;
	if (empty($brick['data'])) $brick['data'] = [];

	$event = mf_activities_form(
		$brick['placeholder'],
		wrap_category_id('event/registration'),
		$brick['data']['website_id'] ?? ''
	);	
	if (!$event) wrap_quit(404);
		
	$brick['data'] = array_merge($brick['data'], $event);
	
	// access
	unset($zz_page['access']);
	if (!empty($brick['data']['website_id']))
		$zz_page['access'][] = sprintf('event_id:%d+website_id:%d', $brick['data']['event_id'], $brick['data']['website_id']);
	else
		$zz_page['access'][] = sprintf('event_id:%d', $brick['data']['event_id']);
	wrap_access_page($zz_page['db']['parameters'] ?? '', $zz_page['access']);

	// breadcrumbs
	$zz_page['breadcrumb_placeholder'][] = [
		'title' => $brick['data']['event'],
		'url_path' => $brick['data']['identifier']
	];
	
	return $brick;
}
