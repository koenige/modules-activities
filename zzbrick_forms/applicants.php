<?php 

/**
 * activities module
 * form script: registrants for a registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['event_id'])) wrap_quit(404);
wrap_include_files('zzform/formkit', 'activities');

$zz = zzform_include_table('contacts');
$zz['sql'] = wrap_edit_sql($zz['sql'], 'JOIN',
	'LEFT JOIN participations USING (contact_id)'
);
$zz['sql'] = wrap_edit_sql($zz['sql'], 'WHERE',
	sprintf('participations.event_id = %d', $brick['data']['event_id'])
);

$zz['title'] = wrap_text('Registrations').' <br><a href="../">'.$brick['data']['event'].'</a>';
$zz['hooks']['after_insert'] = 'mf_activities_formkit_hook';
$zz['page']['data'] = $brick['data'];
$zz['page']['dont_show_title_as_breadcrumb'] = true;

wrap_include_files('zzform/formkit');
$zz = mf_activities_formkit($zz, $brick['data']['event_id'], $brick['data']['form_parameters']);

$zz_conf['delete'] = false;
$zz_conf['add'] = false;
$zz_conf['merge'] = false;

$zz['filter'][1]['sql'] = wrap_edit_sql($zz['filter'][1]['sql'], 'JOIN',
	'LEFT JOIN participations USING (contact_id)'
);
$zz['filter'][1]['sql'] = wrap_edit_sql($zz['filter'][1]['sql'], 'WHERE',
	sprintf('participations.event_id = %d', $brick['data']['event_id'])
);
$zz['filter'][2]['sql'] = wrap_edit_sql($zz['filter'][2]['sql'], 'JOIN',
	'LEFT JOIN participations USING (contact_id)'
);
$zz['filter'][2]['sql'] = wrap_edit_sql($zz['filter'][2]['sql'], 'WHERE',
	sprintf('participations.event_id = %d', $brick['data']['event_id'])
);

// @todo add table participations
