<?php 

/**
 * activities module
 * table script: access
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2023, 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 *
 * Variables
 * translate_pot = admin
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

$zz['fields'][5] = zzform_include('access-usergroups');
$zz['fields'][5]['title'] = 'Groups';
$zz['fields'][5]['type'] = 'subtable';
$zz['fields'][5]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][5]['min_records'] = 1;
$zz['fields'][5]['min_records_required'] = 1;
$zz['fields'][5]['form_display'] = 'lines';
$zz['fields'][5]['list_prefix'] = '<div class="explanation" style="margin: .75em 2.5em; max-width: 40em; font-style: italic;">'.wrap_text('Groups:').' ';
$zz['fields'][5]['list_append_next'] = true;
$zz['fields'][5]['list_suffix'] = '</div>';

$zz['fields'][6]['title'] = 'Included';
$zz['fields'][6]['field_name'] = 'access_key_included';
$zz['fields'][6]['type'] = 'display';
$zz['fields'][6]['format'] = 'mf_activities_access_usergroups_included_record';
$zz['fields'][6]['hide_format_in_title_desc'] = true;
$zz['fields'][6]['list_format'] = 'mf_activities_access_usergroups_included_list';
$zz['fields'][6]['list_prefix'] = '<div class="explanation" style="margin: .75em 2.5em; max-width: 40em; font-style: italic;">';
$zz['fields'][6]['list_suffix'] = '</div>';
$zz['fields'][6]['exclude_from_search'] = true;

$zz['fields'][4]['field_name'] = 'module';
$zz['fields'][4]['default'] = 'custom';

$zz['sql'] = 'SELECT /*_PREFIX_*/access.*
		, access_key as access_key_included
	FROM /*_PREFIX_*/access
';
$zz['sqlorder'] = ' ORDER BY access_key';

$zz['filter'][1]['title'] = wrap_text('Module');
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'module';
$zz['filter'][1]['field_name'] = 'module';
$zz['filter'][1]['sql'] = 'SELECT DISTINCT module, module
	FROM /*_PREFIX_*/access
	ORDER BY module';

$zz['record']['copy'] = true;
