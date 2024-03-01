<?php 

/**
 * activities module
 * contact functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_activities_contact($data, $ids) {
	$sql = 'SELECT participation_id, contact_id
			, usergroup_id, usergroup, identifier
			, date_begin, date_end, remarks, role
		FROM participations
		LEFT JOIN usergroups USING (usergroup_id)
		LEFT JOIN categories
			ON participations.status_category_id = categories.category_id
		WHERE contact_id IN (%s)';
	$sql = sprintf($sql, implode(',', $ids));
	$participations = wrap_db_fetch($sql, 'participation_id');
	// @todo translations
	
	foreach ($participations as $participation_id => $participation) {
		if (empty($data[$participation['contact_id']]['participations'])) {
			$data[$participation['contact_id']]['participation_contact_path']
				= mf_activities_contact_path([
					'identifier' => $data[$participation['contact_id']]['identifier']
					, 'category_parameters' => 'type='.$data[$participation['contact_id']]['scope']
				]);
		}
		$participation['profile_path']
			= mf_activities_group_path(['identifier' => $participation['identifier']]);
		$data[$participation['contact_id']]['participations'][$participation['participation_id']] = $participation;
	}
	$data['templates']['contact_6'][] = 'contact-participations';
	return $data;
}
