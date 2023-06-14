<?php 

/**
 * activities module
 * contact functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_activities_contact($data) {
	$sql = 'SELECT participation_id
			, usergroup_id, usergroup, identifier
			, date_begin, date_end, remarks, role
		FROM participations
		LEFT JOIN usergroups USING (usergroup_id)
		LEFT JOIN categories
			ON participations.status_category_id = categories.category_id
		WHERE contact_id = %d';
	$sql = sprintf($sql, $data['contact_id']);
	$data['participations'] = wrap_db_fetch($sql, 'participation_id');
	foreach ($data['participations'] as $participation_id => $participation) {
		$data['participations'][$participation_id]['profile_path']
			= mf_activities_group_path(['identifier' => $participation['identifier']]);
	}
	$data['participation_contact_path']
		= mf_activities_contact_path([
			'identifier' => $data['identifier']
			, 'category_parameters' => 'type='.$data['scope']
		]);
	$data['templates']['contact_6'][] = 'contact-participations';
	return $data;
}
