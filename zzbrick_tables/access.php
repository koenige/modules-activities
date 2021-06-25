<?php 

/**
 * activities module
 * table script: access
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Access Rights';
$zz['table'] = '/*_PREFIX_*/access';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'access_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Access';
$zz['fields'][2]['field_name'] = 'access_key';
$zz['fields'][2]['type'] = 'write_once';
$zz['fields'][2]['cfg'] = wrap_cfg_files('access');
$zz['fields'][2]['dependencies'] = [3]; // explanation
$zz['fields'][2]['dependencies_function'] = 'mf_activities_access_cfg';
$zz['fields'][2]['list_append_next'] = true;

$zz['fields'][3]['field_name'] = 'explanation';
$zz['fields'][3]['type'] = 'memo';
$zz['fields'][3]['rows'] = 3;
$zz['fields'][3]['list_prefix'] = '<p class="explanation" style="margin: .75em 2.5em; max-width: 40em; "><em>';
$zz['fields'][3]['list_suffix'] = '</em></p>';

$zz['fields'][4] = zzform_include_table('access-usergroups');
$zz['fields'][4]['title'] = 'Usergroups';
$zz['fields'][4]['type'] = 'subtable';
$zz['fields'][4]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][4]['min_records'] = 1;
$zz['fields'][4]['form_display'] = 'lines';

$zz['sql'] = 'SELECT /*_PREFIX_*/access.*
	FROM /*_PREFIX_*/access
';
$zz['sqlorder'] = ' ORDER BY access_key';
