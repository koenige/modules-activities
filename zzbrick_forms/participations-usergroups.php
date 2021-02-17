<?php 

/**
 * Zugzwang Project
 * Table with participations in usergroups
 *
 * http://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


require __DIR__.'/../zzbrick_tables/participations.php';

if (empty($brick['vars'])) wrap_quit(404);

$sql = 'SELECT usergroup_id, usergroup, identifier, usergroups.description
		, categories.parameters AS category_parameters
		, usergroups.parameters AS usergroup_parameters
	FROM usergroups
	LEFT JOIN categories
		ON usergroups.usergroup_category_id = categories.category_id
	WHERE identifier = "%s"';
$sql = sprintf($sql, wrap_db_escape($brick['vars'][0]));
$data = wrap_db_fetch($sql);
if (!$data) wrap_quit(404);

parse_str($data['category_parameters'], $parameters);
parse_str($data['usergroup_parameters'], $u_parameters);
$parameters += $u_parameters;

$zz['where']['usergroup_id'] = $data['usergroup_id'];
$zz['title'] = $data['usergroup'];

$zz['fields'][2]['type'] = 'write_once';

if (!empty($parameters['hide'])) {
	foreach ($parameters['hide'] as $field_name) {
		foreach ($zz['fields'] as $no => $field) {
			if ($field['field_name'] !== $field_name) continue;
			$zz['fields'][$no]['hide_in_form'] = true;
			$zz['fields'][$no]['hide_in_list'] = true;
		}	
	}
}

if (!empty($parameters['value'])) {
	foreach ($parameters['value'] as $field_name => $value) {
		foreach ($zz['fields'] as $no => $field) {
			if ($field['field_name'] !== $field_name) continue;
			$zz['fields'][$no]['type'] = 'hidden';
			if (wrap_substr($field_name, 'category_id', 'end'))
				$zz['fields'][$no]['value'] = wrap_category_id($value);
			else
				$zz['fields'][$no]['value'] = $value;
			$zz['fields'][$no]['hide_in_form'] = true;
			$zz['fields'][$no]['hide_in_list'] = true;
		}
	}
}

$zz['filter'][1]['sql'] = wrap_edit_sql(
	$zz['filter'][1]['sql'], 'WHERE', sprintf('usergroup_id = %d', $data['usergroup_id'])
);
