<?php 

/**
 * activities module
 * contact functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_activities_contact($data, $ids) {
	$data = mf_activities_contact_participations($data, $ids);
	$data = mf_activities_contact_access_rights($data, $ids);
	
	$data['templates']['contact_6'][] = 'contact-participations';
	return $data;
}

/**
 * get participations per contact
 *
 * @param array $data existing data
 * @param array $ids contact IDs
 * @return array
 */
function mf_activities_contact_participations($data, $ids) {
	wrap_include('functions', 'activities');
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
		$participation['profile_path']
			= mf_activities_group_path(['identifier' => $participation['identifier']]);
		$data[$participation['contact_id']]['participations'][$participation['participation_id']] = $participation;
	}
	return $data;
}

/**
 * get access rights per contact
 *
 * @param array $data existing data
 * @param array $ids contact IDs
 * @return array
 */
function mf_activities_contact_access_rights($data, $ids) {
	// is it possible to define access rights?
	$first = reset($data);
	
	$path = wrap_path('activities_contactdata_access['.$first['scope'].']', [], true, true);
	if (!$path) return $data;

	$sql = 'SELECT contact_access_id, contact_id, usergroup
			, access.category AS access_category
			, properties.category AS property_category
			, areas.category AS area_category
			, areas.parameters
		FROM contacts_access
		LEFT JOIN usergroups USING (usergroup_id)
		LEFT JOIN categories access
			ON contacts_access.access_category_id = access.category_id
		LEFT JOIN categories properties
			ON contacts_access.property_category_id = properties.category_id
		LEFT JOIN categories areas
			ON areas.category_id = properties.main_category_id
	    WHERE contact_id IN (%s)';
	$sql = sprintf($sql, implode(',', $ids));
	$access = wrap_db_fetch($sql, 'contact_access_id');
	
	foreach ($access as $contact_access_id => $right) {
		if ($right['parameters']) parse_str($right['parameters'], $right['parameters']);
		if (array_key_exists('activities_access_property_prefix', $right['parameters'])) {
			if ($right['parameters']['activities_access_property_prefix'])
				$right['area_category'] = $right['parameters']['activities_access_property_prefix'];
			else
				$right['area_category'] = '';
		}
		$data[$right['contact_id']]['access'][$contact_access_id] = $right;
	}
	return $data;
}
