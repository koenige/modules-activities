<?php 

/**
 * activities module
 * table script: access
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2022 Gustaf Mossakowski
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
$zz['fields'][2]['dependencies'] = [3, 4]; // explanation, module
$zz['fields'][2]['dependencies_function'] = 'mf_activities_access_cfg';
$zz['fields'][2]['list_append_next'] = true;

$zz['fields'][3]['field_name'] = 'explanation';
$zz['fields'][3]['type'] = 'memo';
$zz['fields'][3]['rows'] = 3;
$zz['fields'][3]['list_prefix'] = '<p class="explanation" style="margin: .75em 2.5em; max-width: 40em; "><em>';
$zz['fields'][3]['list_suffix'] = '</em></p>';
$zz['fields'][3]['list_append_next'] = true;

$zz['fields'][5] = zzform_include_table('access-usergroups');
$zz['fields'][5]['title'] = 'Usergroups';
$zz['fields'][5]['type'] = 'subtable';
$zz['fields'][5]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][5]['min_records'] = 1;
$zz['fields'][5]['min_records_required'] = 1;
$zz['fields'][5]['form_display'] = 'lines';
$zz['fields'][5]['list_prefix'] = '<div class="explanation" style="margin: .75em 2.5em; max-width: 40em; ">'.wrap_text('Groups:').'<em> ';
$zz['fields'][5]['list_suffix'] = '</em></div>';

$zz['fields'][4]['field_name'] = 'module';

$zz['sql'] = 'SELECT /*_PREFIX_*/access.*
	FROM /*_PREFIX_*/access
';
$zz['sqlorder'] = ' ORDER BY access_key';

$zz['filter'][1]['title'] = wrap_text('Module');
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'module';
$zz['filter'][1]['field_name'] = 'module';
$zz['filter'][1]['sql'] = 'SELECT DISTINCT module, module
	FROM /*_PREFIX_*/access';

$zz_conf['copy'] = true;
