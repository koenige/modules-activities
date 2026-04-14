<?php 

/**
 * activities module
 * output of a usergroup
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_usergroup($params, $settings) {
	wrap_include('usergroups', 'activities');
	$data = mf_activities_usergroup($params[0]);
	if (!$data) wrap_quit(404);

	$extra_template = 'usergroups-'.$data['identifier'];
	if (wrap_template_file($extra_template, false)) {
		if (wrap_access('activities_usergroups_tools['.$data['identifier'].']')) {
			$data['template'] = $extra_template;
			// make display more concise
			wrap_setting('zzform_action_icons_hide_labels', true);
		}
	}
	
	$page['title'] = $data['usergroup'];
	$page['breadcrumbs'][]['title'] = $data['usergroup'];
	$page['text'] = wrap_template('usergroup', $data);
	return $page;
}
