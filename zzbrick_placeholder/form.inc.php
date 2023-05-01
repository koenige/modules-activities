<?php 

/**
 * activities module
 * placeholder script for registration form
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_placeholder_form($brick) {
	global $zz_page;
	if (empty($brick['placeholder'])) return $brick;
	if (empty($brick['data'])) $brick['data'] = [];
	
	$sql = 'SELECT event_id, event, identifier, form_id, abstract, events.description
			, IF(published = "yes", 1, NULL) AS published
			, IF((SELECT COUNT(*) FROM formtemplates
				WHERE formtemplates.form_id = forms.form_id
				AND template_category_id = %d)
			>= 1, NULL, IF(form_categories.parameters LIKE "%%&no_authentication_mail=1%%", NULL, 1
			)) AS formtemplates_authentication_missing
			, IF((SELECT COUNT(*) FROM formtemplates
				WHERE formtemplates.form_id = forms.form_id
				AND template_category_id = %d)
			>= 1, NULL, IF(form_categories.parameters LIKE "%%&no_confirmation_mail=1%%", NULL, 1
			)) AS formtemplates_confirmation_missing
			, IF((SELECT COUNT(*) FROM formfields
				WHERE formfields.form_id = forms.form_id)
			>= 2, 1, NULL) AS formfields
			, forms.access
		FROM events
		LEFT JOIN forms USING (event_id)
		LEFT JOIN websites USING (website_id)
		LEFT JOIN categories form_categories
			ON forms.form_category_id = form_categories.category_id
		WHERE identifier = "%s"
	    AND event_category_id = %d
		AND website_id = %d';
	$sql = sprintf($sql
		, wrap_category_id('template-types/authentication')
		, wrap_category_id('template-types/confirmation')
		, wrap_db_escape($brick['placeholder'])
		, wrap_category_id('event/registration')
		, !empty($brick['data']['website_id']) ? $brick['data']['website_id'] : wrap_setting('website_id')
	);
	$event = wrap_db_fetch($sql);
	if (!$event) wrap_quit(404);
	if (!$event['formtemplates_confirmation_missing'] AND !$event['formtemplates_authentication_missing'])
		$event['formtemplates'] = true;
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
