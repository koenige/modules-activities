<?php 

/**
 * activities module
 * send a mail related to a form
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/** 
 * send a mail related to a form
 * 
 * @param array @params
 *		[0]: event_id
 *		[1]: contact_id
 *		[2]: type of template
 *		[3]: (optional) formfield_id
 * @return array
 */
function mod_activities_make_formmail($params) {
	if (count($params) < 3) return false;
	if ($params[2] === 'field-changed') {
		if (count($params) !== 4) return false;
	} elseif (count($params) !== 3) return false;

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$page['text'] = wrap_template('formmail');
		return $page;
	}
	
	if (str_ends_with($params[2], '-reminder')) {
		$params[2] = substr($params[2], 0, strlen('-reminder'));
		$extra_message = wrap_text('Reminder –', wrap_setting('lang')).' ';
	} else {
		$extra_message = '';
	}
	if (!in_array($params[2], ['authentication', 'confirmation', 'field-changed']))
		wrap_error(sprintf('Unknown form mail type %s.', $params[2]), E_USER_ERROR);

	$data = mod_activities_formmail_prepare($params[0], $params[1], $params[2], $params[3] ?? NULL);
	if (!$data) {
		$page['text'] = sprintf(wrap_text('Contact ID %d for form mail (%s), event ID %d, not found.'), $params[1], $params[2], $params[0]);
		$page['status'] = 404;
		return $page;
	}

	// @todo get e_mail from event
	$data['sender'] = wrap_setting('own_name');
	$mail['headers']['From']['name'] = $data['sender'];
	$mail['headers']['From']['e_mail'] = $data['sender_mail'] ?? wrap_setting('own_e_mail');

	$mailtitle = $data['form_parameters']['formmail_subject'][$params[2]] ?? '';
	if (!$mailtitle AND !empty($data['fieldtitle'])) // field-changed mail
		$mailtitle = $data['fieldtitle'];
	$mail['subject'] = $data['event'].': '.$extra_message.wrap_text($mailtitle);
	$mail['message'] = wrap_template($data['formmail_template']."\n", $data);
	$mail['headers']['Bcc'] = wrap_setting('mail_bcc');

	// check if there is a custom recipient list
	$recipients = $data['recipients'] ?? [];
	if (!$recipients) {
		if ($data['e_mail']) {
			$recipients[] = [
				'contact' => $data['contact'],
				'e_mail' => $data['e_mail']
			];
		} else {
			// no mail? wait for some time, try to send again if there are mails
			// increase try_no so job automatically stops at some point
			wrap_job(wrap_setting('request_uri'), [
				'wait_until' => wrap_setting('activities_formmail_wait_no_address_seconds'),
				'try_no_increase' => 1
			]);
			$page['text'] = '<p>'.wrap_text('Form mail: No recipient email address found.').'</p>';
			$page['status'] = 404;
			return $page;
		}
	}
	$mails_failed = [];
	foreach ($recipients as $recipient) {
		$mail['to']['name'] = $recipient['contact'];
		$mail['to']['e_mail'] = $recipient['e_mail'];

		$success = wrap_mail($mail);
		if (!$success) $mails_failed[] = $recipient['e_mail'];
	}
	if ($mails_failed) {
		wrap_error(sprintf(
			'%s mails could not be sent to %s (ID %d)', ucfirst($params[2]), implode(',', $mails_failed), $data['contact_id']
		));
		$page['text'] = wrap_text('Failed to send mail.');
		$page['status'] = 404;
		return $page;
	}
	if (wrap_setting('activities_formmail_sender_copy['.$params[2].']')) {
		$mail['to'] = $data['sender'];
		$mail['subject'] .= ' '.wrap_text('(copy of e-mail)');
		$mail_copy_text = wrap_text('This e-mail was just sent to:')."\r\n";
		foreach ($recipients as $recipient) {
			$mail_copy_text .= $recipient['contact'].' <'.$recipient['e_mail'].">\r\n";
		}
		$mail['message'] = $mail_copy_text."\r\n\r\n".$mail['message'];
		$success = wrap_mail($mail);
		if (!$success)
			wrap_error(sprintf(
				'%s mail could not be sent as copy to %s (ID %d)', ucfirst($params[2]), $data['sender'], $data['contact_id']
			));
	}
	
	mod_activities_formmail_log($data, $params[2]);

	$page['text'] = wrap_text('Mail was successfully sent.');
	$page['status'] = 200;
	return $page;
}

/**
 * log that a form mail was sent
 *
 * @param array $data
 *		for contactverifications: int contact_id
 *		otherwise: int participation_id
 * @param string $type
 */
function mod_activities_formmail_log($data, $type) {
	if (wrap_setting('activities_use_contactverifications')) {
		// @deprecated
		if ($type === 'authentication')
			mod_activities_make_formmail_log_cv($data['contact_id']);
		return;
	}
	$values = [];
	$values['action'] = 'insert';
	$values['ids'] = ['participation_id', 'activity_category_id'];
	$values['POST']['participation_id'] = $data['participation_id'];
	$values['POST']['activity_category_id'] = wrap_category_id('activities/mail');
	$ops = zzform_multi('activities', $values);
	if (!$ops['id'])
		wrap_error(sprintf('Unable to add activity mail to participation ID %d', $data['participation_id']));
}

/**
 * update contacts_verifications.mails_sent
 *
 * @param int $contact_id
 */
function mod_activities_make_formmail_log_cv($contact_id) {
	$sql = 'UPDATE contacts_verifications SET mails_sent = mails_sent + 1 WHERE contact_id = %d';
	$sql = sprintf($sql, $contact_id);
	$result = wrap_db_query($sql);
	if (!$result) return;
	wrap_include_files('database', 'zzform');
	zz_log_sql($sql);
}

/**
 * get fields for confirmation and authentication mails
 *
 * @param int $event_id
 * @param int $contact_id
 * @param string $type
 * @param int $formfield_id
 * @return array $data
 */
function mod_activities_formmail_prepare($event_id, $contact_id, $type, $formfield_id = NULL) {
	// person
	if (wrap_setting('activities_use_contactverifications')) {
		$sql = 'SELECT contacts.contact_id
				, first_name, last_name, contact
				, IF(sex = "male", 1, NULL) AS male
				, IF(sex = "female", 1, NULL) AS female
				, IF(sex = "diverse", 1, NULL) AS diverse
				, IF(ISNULL(sex), 1, NULL) AS unknown
				, identification AS e_mail
				, verification_hash
				, languages.iso_639_1
			FROM contacts
			LEFT JOIN persons USING (contact_id)
			LEFT JOIN contacts_verifications USING (contact_id)
			LEFT JOIN languages USING (language_id)
			LEFT JOIN contactdetails
				ON contactdetails.contact_id = contacts.contact_id
				AND contactdetails.provider_category_id = %d
			WHERE contacts.contact_id = %d';
	} else {
		$sql = 'SELECT contacts.contact_id
				, first_name, last_name, contact
				, IF(sex = "male", 1, NULL) AS male
				, IF(sex = "female", 1, NULL) AS female
				, IF(sex = "diverse", 1, NULL) AS diverse
				, IF(ISNULL(sex), 1, NULL) AS unknown
				, identification AS e_mail
				, participation_id
				, verification_hash
				, entry_date
			FROM contacts
			LEFT JOIN persons USING (contact_id)
			LEFT JOIN participations USING (contact_id)
			LEFT JOIN contactdetails
				ON contactdetails.contact_id = contacts.contact_id
				AND contactdetails.provider_category_id = %d
			WHERE contacts.contact_id = %d';
	}
	$sql = sprintf($sql, wrap_category_id('provider/e-mail'), $contact_id);
	$data = wrap_db_fetch($sql);
	if (!$data) return [];

	// set mail language depending on registration process, not current or default language
	if (!empty($data['iso_639_1']))
		wrap_setting('lang', $data['iso_639_1']);
	
	$sql = 'SELECT event_id, event, events.identifier
			, CONCAT(IFNULL(date_begin, ""), "/", IFNULL(date_end, "")) AS duration
			, form_id
			, IF(address = "formal", 1, NULL) AS formal_address
			, IF(address = "informal", 1, NULL) AS informal_address
			, formcategories.category AS form_category
			, formcategories.parameters AS form_parameters
			, events.parameters AS event_parameters
	    FROM events
		LEFT JOIN forms USING (event_id)
		LEFT JOIN categories formcategories
			ON formcategories.category_id = forms.form_category_id
	    WHERE event_id = %d';
	$sql = sprintf($sql
		, $event_id
	);
	$event = wrap_db_fetch($sql);
	if ($event['informal_address']) wrap_setting('language_variation', 'informal');
	$event = wrap_translate($event, 'events');
	if ($event['form_parameters'])
		parse_str($event['form_parameters'], $event['form_parameters']);
	if ($event['event_parameters'])
		wrap_module_parameters('events', $event['event_parameters']);
	$data = array_merge($data, $event);

	$data['duration'] = wrap_date($data['duration']);
	$data['formmail_template'] = mf_activities_form_templates($data['form_id'], $type, $formfield_id);
	if (!$data['formmail_template']) return false;
	$data['values'] = mf_activities_formfielddata($contact_id, $data['form_id']);
	if ($formfield_id) {
		$data['fieldvalue'] = $data['values'][$formfield_id]['value'];
		$data['fieldtitle'] = $data['values'][$formfield_id]['formfield'];
	}

	$data['authentication_link'] = wrap_setting('host_base').wrap_path('activities_registration_confirmation', [], false).sprintf('?confirm=%s', $data['verification_hash']);
	$data['rejection_link'] = wrap_setting('host_base').wrap_path('activities_registration_confirmation', [], false).sprintf('?delete=%s', $data['verification_hash']);

	// custom data?	
	if (function_exists('my_formmail_prepare'))
		$data = my_formmail_prepare($data);
	
	return $data;
}
