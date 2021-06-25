<?php 

/**
 * activities module
 * table script: participations in usergroups
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
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
$zz['explanation'] = markdown($data['description']);

$zz['fields'][2]['type'] = 'write_once';

$zz['fields'][9]['type'] = 'sequence';

$zz['filter'][1]['sql'] = wrap_edit_sql(
	$zz['filter'][1]['sql'], 'WHERE', sprintf('usergroup_id = %d', $data['usergroup_id'])
);

if (!empty($parameters['filter_mail'])) {
	$zz['filter'][3]['title'] = wrap_text('E-Mail');
	$zz['filter'][3]['identifier'] = 'mail';
	$zz['filter'][3]['type'] = 'list';
	$zz['filter'][3]['where'] = 'identification';
	$zz['filter'][3]['sql_join'] = sprintf('LEFT JOIN /*_PREFIX_*/contactdetails
		ON /*_PREFIX_*/contactdetails.contact_id = /*_PREFIX_*/participations.contact_id
		AND provider_category_id = %d'
		, wrap_category_id('provider/e-mail')
	);
	$zz['filter'][3]['selection']['!NULL'] = wrap_text('with E-Mail');
	$zz['filter'][3]['selection']['NULL'] = wrap_text('without E-Mail');
}
