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
$zz['sql'] = wrap_edit_sql($zz['sql'], 'JOIN',
	sprintf('LEFT JOIN activities verifications
		ON participations.participation_id = verifications.participation_id
		AND verifications.activity_category_id = %d', wrap_category_id('activities/verify'))
);
$zz['sql'] = wrap_edit_sql($zz['sql'], 'JOIN',
	sprintf('LEFT JOIN activities subscriptions
		ON participations.participation_id = subscriptions.participation_id
		AND subscriptions.activity_category_id = %d', wrap_category_id('activities/subscribe'))
);
$zz['sql'] = wrap_edit_sql($zz['sql'], 'SELECT',
	'verifications.activity_ip AS verify_activity_ip
	, verifications.activity_date AS verify_activity_date
	, subscriptions.activity_ip AS subscribe_activity_ip'
);

$zz['title'] = wrap_text('Registrations').' <br><a href="../">'.$brick['data']['event'].'</a>';
$zz['hooks']['after_insert'] = 'mf_activities_formkit_hook';
$zz['page']['data'] = $brick['data'];
$zz['page']['dont_show_title_as_breadcrumb'] = true;

wrap_include_files('zzform/formkit');
$zz = mf_activities_formkit($zz, $brick['data']['event_id'], $brick['data']['form_parameters']);

foreach (array_keys($zz['fields']) as $no) continue;
$zz['fields'][++$no] = mf_activities_formkit_participations($brick['data']['event_id']);

// export fields
$zz['fields'][++$no] = [
	'title' => 'Entry IP',
	'type' => 'display',
	'type_detail' => 'ip',
	'field_name' => 'subscribe_activity_ip',
	'search' => 'subscriptions.activity_ip',
	'character_set' => 'latin1',
	'hide_in_list' => true
];
$zz['fields'][++$no] = [
	'title' => 'Verification',
	'type' => 'display',
	'type_detail' => 'datetime',
	'field_name' => 'verify_activity_date',
	'search' => 'verifications.activity_date',
	'character_set' => 'latin1',
	'hide_in_list' => true
];
$zz['fields'][++$no] = [
	'title' => 'Verification IP',
	'type' => 'display',
	'type_detail' => 'ip',
	'field_name' => 'verify_activity_ip',
	'search' => 'verifications.activity_ip',
	'character_set' => 'latin1',
	'hide_in_list' => true
];

// @todo change where to something else, records must not be deleted completely if
// contact_id is used from other parts of system
$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = sprintf('participations.status_category_id = %d', wrap_category_id('participation-status/subscribed'));

$zz_conf['delete'] = false;
$zz_conf['add'] = false;
$zz_conf['merge'] = false;

$zz_conf['if'][1]['delete'] = true;

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
