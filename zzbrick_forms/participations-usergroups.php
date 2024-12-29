<?php 

/**
 * activities module
 * table script: participations in usergroups
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['vars'])) wrap_quit(404);
if (strstr($brick['vars'][0], '/')) {
	$brick['vars'][0] = explode('/', $brick['vars'][0]);
	$brick['vars'][0] = end($brick['vars'][0]);
}

require __DIR__.'/../zzbrick_tables/participations.php';

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

if ($data['category_parameters'])
	parse_str($data['category_parameters'], $parameters);
else
	$parameters = [];
if ($data['usergroup_parameters'])
	parse_str($data['usergroup_parameters'], $u_parameters);
else
	$u_parameters = [];
$parameters += $u_parameters;

if (!empty($parameters['access']))
	$zz['access'] = $parameters['access'];

$zz['where']['usergroup_id'] = $data['usergroup_id'];
$zz['title'] = $data['usergroup'];
$zz['explanation'] = markdown($data['description']);

$zz['fields'][2]['type'] = 'write_once';

$zz['fields'][9]['type'] = 'sequence';

if (!empty($parameters['hide']['status_category_id']))
	$zz['fields'][6]['hide_in_list'] = true;

$zz['filter'][1]['sql'] = wrap_edit_sql(
	$zz['filter'][1]['sql'], 'WHERE', sprintf('usergroup_id = %d', $data['usergroup_id'])
);

// search: postcode
$zz['fields'][13]['field_name'] = 'postcode';
$zz['fields'][13]['type'] = 'display';
$zz['fields'][13]['hide_in_list'] = true;
$zz['fields'][13]['hide_in_form'] = true;
$zz['fields'][13]['search'] = '(SELECT postcode FROM addresses WHERE addresses.contact_id = participations.contact_id LIMIT 1)';


if (!empty($parameters['filter_mail'])) {
	$zz['filter'][3]['title'] = wrap_text('E-Mail');
	$zz['filter'][3]['identifier'] = 'mail';
	$zz['filter'][3]['type'] = 'list';
	$zz['filter'][3]['where'] = 'identification';
	$zz['filter'][3]['sql_join'] = 'LEFT JOIN /*_PREFIX_*/contactdetails
		ON /*_PREFIX_*/contactdetails.contact_id = /*_PREFIX_*/participations.contact_id
		AND provider_category_id = /*_ID categories provider/e-mail_*/';
	$zz['filter'][3]['selection']['!NULL'] = wrap_text('with E-Mail');
	$zz['filter'][3]['selection']['NULL'] = wrap_text('without E-Mail');
}
