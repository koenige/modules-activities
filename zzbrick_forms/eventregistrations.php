<?php 

/**
 * activities module
 * form script: registrations, based on events
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include_table('events/events');
$zz['title'] = 'Registrations';
$zz['where']['event_category_id'] = wrap_category_id('event/registration');

if (!empty($brick['data']['website_id']))
	$zz['where']['website_id'] = $brick['data']['website_id'];

unset($zz['fields'][4]); // date_begin
unset($zz['fields'][5]); // date_end

unset($zz['fields'][54]); // time_begin
unset($zz['fields'][55]); // time_end
unset($zz['fields'][56]); // timezone
unset($zz['fields'][53]); // event_year

unset($zz['fields'][6]['separator_before']);
$zz['fields'][6]['title'] = 'Registration';
$zz['fields'][6]['link'] = [
	'string1' => '',
	'field1' => 'identifier',
	'string2' => '/'
];
unset($zz['fields'][8]['explanation']);

unset($zz['fields'][7]); // place

// event_category_id
$zz['fields'][26]['value'] = wrap_category_id('event/registration');

unset($zz['fields'][24]); // categories

unset($zz['fields'][20]); // takes_place

unset($zz['fields'][23]); // show_in_news

unset($zz['fields'][62]); // event_media

unset($zz['fields'][16]); // direct_link

unset($zz['fields'][17]); // registration

unset($zz['fields'][9]); // main_event_id

unset($zz['filter'][2]);
unset($zz['filter'][3]);
