<?php 

/**
 * activities module
 * output of a single form
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_form($params) {
	if (count($params) !== 1) return false;

	$sql = 'SELECT event_id, event, identifier, abstract, description
			, IF (published = "yes", 1, NULL) AS published
		FROM events
	    WHERE identifier = "%s"
	    AND event_category_id = %d';
	$sql = sprintf($sql
		, wrap_db_escape($params[0])
		, wrap_category_id('event/registration')
	);
	$data = wrap_db_fetch($sql);
	$page['title'] = $data['event'];
	$page['text'] = wrap_template('form', $data);
	return $page;
}
