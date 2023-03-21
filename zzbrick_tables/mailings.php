<?php 

/**
 * activities module
 * table script: mailings per event
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2012, 2018-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Mailings';
$zz['table'] = 'mailings';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'mailing_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id, event
		, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
	FROM events
	WHERE ISNULL(main_event_id)
	ORDER BY event';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['if']['where']['class'] = 'hidden';

$zz['fields'][4]['title'] = 'Sender';
$zz['fields'][4]['field_name'] = 'sender_contact_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT contact_id, contact
	, (SELECT identification FROM contactdetails
			WHERE contactdetails.contact_id = contacts.contact_id
			AND provider_category_id = %d
			LIMIT 1
		) AS e_mail
	FROM /*_PREFIX_*/persons
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	ORDER BY last_name, first_name', wrap_category_id('provider/e-mail'));
$zz['fields'][4]['display_field'] = 'contact';
$zz['fields'][4]['default'] = $_SESSION['contact_id'];
$zz['fields'][4]['list_append_next'] = true;

// @todo only use to save sender_mail, but allow to choose from mail in sender_contact_id
// e. g. replace this field with sender_contactdetail_id
$zz['fields'][14]['title'] = 'E-mail';
$zz['fields'][14]['field_name'] = 'sender_mail';
$zz['fields'][14]['explanation'] = wrap_text('Alternative e-mail address for sending the e-mails if you do not want to send from your own address.');
if ($suffix = wrap_setting('activities_mailings_suffix_alternative_from'))
	$zz['fields'][14]['explanation'] .= ' '.sprintf(wrap_text('In this case “, %s” is appended to the name.'), $suffix);
$zz['fields'][14]['list_prefix'] = '<br>&lt;';
$zz['fields'][14]['list_suffix'] = '&gt;';

$zz['fields'][13]['field_name'] = 'subject';
$zz['fields'][13]['size'] = 60;
if ($prefix = wrap_setting('mail_subject_prefix'))
	$zz['fields'][13]['prefix'] = $prefix.' ';
$zz['fields'][13]['list_prefix'] = '<p><strong>';
$zz['fields'][13]['list_suffix'] = '</strong></p>';
$zz['fields'][13]['list_append_next'] = true;

$zz['fields'][3]['field_name'] = 'message';
$zz['fields'][3]['type'] = 'memo';
$zz['fields'][3]['list_prefix'] = '<div class="moretext">';
$zz['fields'][3]['list_suffix'] = '</div>';
$zz['fields'][3]['format'] = 'nl2br';
$zz['fields'][3]['hide_format_in_title_desc'] = true;
$zz['fields'][3]['rows'] = 30;
$zz['fields'][3]['cols'] = 80;
$zz['fields'][3]['explanation'] = sprintf(wrap_text('%d characters per line.'), $zz['fields'][3]['cols']);
if ($path = wrap_setting('activities_mailings_help')) {
	$zz['fields'][3]['explanation'] .= ' '.sprintf('(<a href="%s">Possible placeholders</a>)', $path);
}
$zz['fields'][3]['list_append_next'] = true;

$zz['fields'][5] = zzform_include_table('mailings-contacts');
$zz['fields'][5]['title'] = 'Recipients';
$zz['fields'][5]['type'] = 'subtable';
$zz['fields'][5]['min_records'] = 1;
$zz['fields'][5]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][5]['fields'][3]['show_title'] = false;
$zz['fields'][5]['fields'][4]['class'] = 'hidden';
$zz['fields'][5]['fields'][4]['for_action_ignore'] = true;
$zz['fields'][5]['subselect']['sql'] = 'SELECT mailing_id, contact
	FROM mailings_contacts
	LEFT JOIN mailings USING (mailing_id)
	LEFT JOIN persons
		ON persons.contact_id = mailings_contacts.recipient_contact_id
	LEFT JOIN contacts USING (contact_id)
	ORDER BY last_name, first_name
';
$zz['fields'][5]['subselect']['concat_rows'] = ', ';
$zz['fields'][5]['list_prefix'] = sprintf('<hr><div class="activities_mailing_recipients">%s:', wrap_text('Recipients'));
$zz['fields'][5]['list_suffix'] = '</div>';

$zz['fields'][6]['field_name'] = 'sent';
$zz['fields'][6]['type'] = 'hidden';
$zz['fields'][6]['type_detail'] = 'datetime';
$zz['fields'][6]['display_field'] = 'status';

$zz['fields'][7]['show_title'] = false;
$zz['fields'][7]['field_name'] = 'send_mailings';
$zz['fields'][7]['type'] = 'option';
$zz['fields'][7]['class'] = 'explanation';
$zz['fields'][7]['type_detail'] = 'select';
$zz['fields'][7]['set'] = ['Send (if not clicked, mail will only be saved for later sending)'];
$zz['fields'][7]['exclude_from_search'] = true;
$zz['fields'][7]['if'][1] = false;

$zz['fields'][20]['field_name'] = 'last_update';
$zz['fields'][20]['type'] = 'timestamp';
$zz['fields'][20]['hide_in_list'] = true;

$zz['sql'] = sprintf('SELECT mailings.*
		, event
		, contact
		, IFNULL(sent, "%s") AS status
	FROM mailings
	LEFT JOIN events USING (event_id)
	LEFT JOIN contacts
		ON contacts.contact_id = mailings.sender_contact_id
', wrap_text('not yet'));
$zz['sqlorder'] = ' ORDER BY events.identifier DESC, sent, last_update';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = 'NOT ISNULL(sent)';

$zz['hooks']['before_insert'] = 
$zz['hooks']['before_update'] = 'mf_activities_hook_mailing_send';

$zz_conf['copy'] = true;
$zz_conf['if'][1]['edit'] = false;
$zz_conf['if'][1]['delete'] = false;
wrap_setting('zzform_max_detail_records', 200); // max recipients, adapt if needed

$zz['subtitle']['event_id']['sql'] = $zz['fields'][2]['sql'];
$zz['subtitle']['event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['event_id']['link'] = '../';
$zz['subtitle']['event_id']['link_no_append'] = true;

$zz['explanation'] = sprintf('<p><em>(%s)</em></p>', wrap_text('Clicking on the mail text shows the full mail text or just a short excerpt from the mail text'));
