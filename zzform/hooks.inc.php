<?php 

/**
 * activities module
 * hooks for tables
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * copy formfields when a form is copied as well
 *
 * @param array $ops
 *		'copy_formfields' => not none, but integer
 * @return bool
 */
function mf_activities_copy_formfields($ops) {
	global $zz_conf;

	foreach ($ops['return'] as $index => $table) {
		if ($table['table'] !== 'forms') continue;
		if ($table['action'] !== 'insert') continue;
		if (empty($ops['record_new'][$index]['copy_formfields'])) continue;
		if ($ops['record_new'][$index]['copy_formfields'] === 'none') continue;

		// existing fields
		$sql = 'SELECT * FROM formfields WHERE form_id = %d';
		$sql = sprintf($sql, $ops['record_new'][$index]['copy_formfields']);
		$data = wrap_db_fetch($sql, 'formfield_id');
		if (!$data) continue;
		
		$values = [];
		$values['action'] = 'insert';
		$values['ids'] = ['formfield_category_id', 'form_id'];
		$values['POST']['form_id'] = $ops['id'];
		foreach ($data as $line) {
			foreach ($line as $field_name => $value) {
				if (in_array($field_name, ['formfield_id', 'form_id', 'last_update'])) continue;
				$values['POST'][$field_name] = $value;
			}
			$n_ops = zzform_multi('formfields', $values);
			if (!$n_ops['id']) {
				wrap_error(sprintf('Could not copy formfield ID %d', $line['formfield_id']));
			}
			// map old fields to new fields for translations
			$map[$line['formfield_id']] = $n_ops['id'];
		}
		mf_activities_copy_translations('formfields', array_keys($data), $map);
	}
	return true;
}

/**
 * copy translations for a table
 *
 * @param string $table name of table
 * @param array $ids IDs of fields to translate
 * @param array $map mapping of 
 * @return bool
 */
function mf_activities_copy_translations($table, $ids, $map) {
	global $zz_conf;

	$sql = 'SELECT translationfield_id, field_type
		FROM %s
		WHERE db_name = "%s" AND table_name = "%s"';
	$sql = sprintf($sql
		, wrap_sql_table('default_translationfields')
		, $zz_conf['db_name']
		, $table
	);
	$translationfields = wrap_db_fetch($sql, 'translationfield_id');
	if (!$translationfields) return false;
	foreach ($translationfields as $field) {
		$sql = 'SELECT translation_id, field_id, translation, language_id
			FROM _translations_%s
			WHERE translationfield_id = %d
			AND field_id IN (%s)';
		$sql = sprintf($sql
			, $field['field_type']
			, $field['translationfield_id']
			, implode(',', $ids)
		);
		$translations = wrap_db_fetch($sql, 'translation_id');
		$values = [];
		$values['action'] = 'insert';
		$values['ids'] = ['translationfield_id', 'language_id'];
		$values['POST']['translationfield_id'] = $field['translationfield_id'];
		foreach ($translations as $translation) {
			$values['POST']['field_id'] = $map[$translation['field_id']];
			$values['POST']['translation'] = $translation['translation'];
			$values['POST']['language_id'] = $translation['language_id'];
			$n_ops = zzform_multi(sprintf('translations-%s', $field['field_type']), $values);
			if (!$n_ops['id']) {
				wrap_error(sprintf('Could not copy translation for table %s ID %d', $table, $map[$translation['field_id']]));
			}
		}
	}
	return true;
}

/**
 * send confirmation mail for registration
 * add activity for registration
 * 
 * @param array $ops
 * @return array
 */
function mf_activities_confirm_registration($ops) {
	global $zz_conf;

	// get registrant’s data
	$data = [];
	$events = [];

	foreach ($ops['return'] as $index => $table) {
		switch ($table['table']) {
			case 'contacts':
				$data['contact'] = $ops['record_new'][$index]['contact'];
				$data['contact_id'] = $ops['record_new'][$index]['contact_id'];
				$zz_conf['user'] = $ops['record_new'][$index]['identifier'];
				break;
			case 'contactdetails':
				if ($ops['record_new'][$index]['provider_category_id'] !== wrap_category_id('provider/e-mail')) break;
				$data['e_mail'] = $ops['record_new'][$index]['identification'];
				break;
			case 'participations':
				$data['verification_hash'][] = $ops['record_new'][$index]['verification_hash'];
				$data['participation_id'][] = $ops['record_new'][$index]['participation_id'];
				$events[] = $ops['record_new'][$index]['event_id'];
				break;
		}
	}

	// write activities
	foreach ($data['participation_id'] as $participation_id) {
		$values = [];
		$values['action'] = 'insert';
		$values['ids'] = ['activity_category_id'];
		$values['POST']['participation_id'] = $participation_id;
		$values['POST']['activity_category_id'] = wrap_category_id('activities/subscribe');
		$values['POST']['activity_uri'] = sprintf('mailto:%s', $data['e_mail']);
		$activity = zzform_multi('activities', $values);
		if (!$activity['id'])
			wrap_error(sprintf('The registration for %s was not completed.', $data['e_mail']), E_USER_ERROR);
	}

	// get events
	if ($events) {
		$sql = 'SELECT event_id, event
				, CONCAT(IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
			FROM events
			WHERE event_id IN (%s)';
		$sql = sprintf($sql, implode(',', $events));
		$data['events'] = wrap_db_fetch($sql, 'event_id');
	}

	$data['verification_hash'] = implode('-', $data['verification_hash']);

	$mail['to']['name'] = $data['contact'];
	$mail['to']['e_mail'] = $data['e_mail'];
	
	$data['sender'] = wrap_get_setting('own_name');
	if (!$data['sender']) $data['sender'] = wrap_get_setting('project');
	$mail['headers']['From']['name'] = $data['sender'];
	$mail['headers']['From']['e_mail'] = wrap_get_setting('own_e_mail');

	// @todo use custom confirmation mails from forms-table
	$mail['message'] = wrap_template('registration-confirmation-mail', $data);
	$success = wrap_mail($mail);
	if (!$success) {
		wrap_error(sprintf(
			'Registration mail could not be sent to %s (ID %d)', $data['e_mail'], $data['contact_id']
		));
	}

	return [];
}

//
// ---- Mailings ----
//

/**
 * read e-mail addresses from database for mailing
 *
 * @param array $ops
 * @return array
 */
function mf_activities_hook_mailing_add_addresses($ops) {
	$participation_ids = [];
	foreach ($ops['not_validated'] as $index => $table) {
		if ($table['table'] !== 'mailings_contacts') continue;
		if (!empty($ops['record_new'][$index]['recipient_mail'])) continue;
		$participation_ids[$index] = $ops['record_new'][$index]['recipient_contact_id'];
	}
	if (!$participation_ids) return [];
	
	$sql = 'SELECT participation_id, contact_id, identification
		FROM participations
		LEFT JOIN contactdetails USING (contact_id)
		WHERE participation_id IN (%s)
		AND contactdetails.provider_category_id = %d';
	$sql = sprintf($sql
		, implode(',', $participation_ids)
		, wrap_category_id('provider/e-mail')
	);
	$mails = wrap_db_fetch($sql, 'participation_id');
	if (!$mails) return [];
	
	$change = [];
	$contact_ids = [];
	foreach ($participation_ids as $index => $participation_id) {
		if (empty($mails[$participation_id])) {
			$change['no_validation'] = true;
			continue;
		}
		if (in_array($mails[$participation_id]['contact_id'], $contact_ids)) {
			// remove duplicate selections (contact might be in more than one group)
			$change['record_replace'][$index]['recipient_contact_id'] = false;
			$change['record_replace'][$index]['recipient_mail'] = false;
		} else {
			$change['record_replace'][$index]['recipient_contact_id'] = $mails[$participation_id]['contact_id'];
			$change['record_replace'][$index]['recipient_mail'] = $mails[$participation_id]['identification'];
		}
		$contact_ids[] = $mails[$participation_id]['contact_id'];
	}
	return $change;
}

/**
 * send mailings
 *
 * @param array $ops
 * @return array
 */
function mf_activities_hook_mailing_send($ops) {
	global $zz_setting;
	
	if (empty($ops['record_new'][0])) return false;
	$maildata = array_shift($ops['record_new']);
	if (empty($maildata['send_mailings'][1])) return false;

	$mail = [];
	$mail['message'] = $maildata['message']; 
	$mail['subject'] = $maildata['subject'];
	
	// get sender
	$sql = 'SELECT identification AS e_mail
			, contact AS name
		FROM contacts
		LEFT JOIN contactdetails USING (contact_id)
		WHERE contact_id = %d
		AND provider_category_id = %d';
	$sql = sprintf($sql
		, $maildata['sender_contact_id']
		, wrap_category_id('provider/e-mail')
	);
	$mail['headers']['From'] = wrap_db_fetch($sql, '', 'key/value');
	if (empty($mail['headers']['From'])) return false;
	if ($maildata['sender_mail']) {
		if ($suffix = wrap_get_setting('activities_mailings_suffix_alternative_from'))
			$mail['headers']['From']['name'] .= sprintf(', %s', $suffix);
		$mail['headers']['From']['e_mail'] = $maildata['sender_mail'];
	}
	
	// get event
	$sql = sprintf(wrap_sql_query('activities_mailings_event'), $maildata['event_id']);
	$event = wrap_db_fetch($sql);
	if (!empty($event['duration']))
		$event['duration'] = html_entity_decode(wrap_date($event['duration']), ENT_QUOTES, 'UTF-8');

	// get all recipients
	$recipient_contact_ids = [];
	foreach ($ops['record_new'] as $rec)
		$recipient_contact_ids[] = $rec['recipient_contact_id'];

	// @todo add usergroup_id, currently it might happen that some records
	// are combined, so some data in JOINed tables might be missing
	$sql = sprintf(wrap_sql_query('activities_mailings_recipients')
		, implode(',', $recipient_contact_ids)
		, $event['event_id']
	);
	$recipients = wrap_db_fetch($sql, 'contact_id');

	// apply new text formatting
	$old_brick_fulltextformat = wrap_get_setting('brick_fulltextformat');
	$zz_setting['brick_fulltextformat'] = 'brick_textformat_html';

	// @todo sende eine Kopie der ersten Mail an den Absender!
	foreach ($ops['record_new'] as $rec) {
		$my_mail = $mail;
		$recipient = $recipients[$rec['recipient_contact_id']];
		$my_mail['to']['name'] = $recipient['name'];
		$my_mail['to']['e_mail'] = $recipient['e_mail'];

		$my_data = $event + $recipient;
		$my_data['addlogin_hash'] = wrap_set_hash($my_data['contact_id'].'-'.$my_data['identifier'], 'addlogin_key');
		
		// call custom function if exists
		if (function_exists('my_hook_mailing_send'))
			$my_data = my_hook_mailing_send($my_data);

		$msg = brick_format($my_mail['message'], $my_data);
		$my_mail['message'] = $msg['text'];
		$success = wrap_mail($my_mail);
		if (!$success) {
			wrap_error(sprintf(
				'Unable to send mail with ID %d to recipient with ID %d (%s).',
				$maildata['mailing_id'], $recipient['contact_id'], $recipient['name'])
			);
		}
	}
	$zz_setting['brick_fulltextformat'] = $old_brick_fulltextformat;
	$record['record_replace'][0]['sent'] = date('Y-m-d H:i:s');
	return $record;
}
