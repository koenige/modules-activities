<?php 

/**
 * activities module
 * Confirm a registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_make_registrationconfirmation() {
	wrap_setting('cache', false);
	wrap_setting_add('extra_http_headers', 'X-Frame-Options: Deny');
	wrap_setting_add('extra_http_headers', "Content-Security-Policy: frame-ancestors 'self'");

	$form = [];
	$form['reminder'] = false;
	$form['action'] = parse_url(wrap_setting('request_uri'), PHP_URL_PATH);

	$possible_actions = ['confirm', 'delete'];
	$page['query_strings'] = ['code', 'action', 'confirm', 'delete'];

	// What to do?
	if (!empty($_GET['code']) && !empty($_GET['action'])
		&& in_array($_GET['action'], $possible_actions)) {
		$form['code'] = $_GET['code'];
		$action = $_GET['action'];
	} elseif (!empty($_GET['confirm'])) {
		$form['code'] = $_GET['confirm'];
		$action = 'confirm';
	} elseif (!empty($_GET['delete'])) {
		$form['code'] = $_GET['delete'];
		$action = 'delete';
	} elseif (!empty($_GET['code'])) {
		$form['code'] = $_GET['code'];
		$form['form'] = true;
		$action = false;
		$form['reminder'] = true;
	} else {
		$form['code'] = false;
		$form['form'] = true;
		$page['text'] = wrap_template('registration-confirmation', $form);
		return $page;
	}
	
	$form['codes'] = explode('-', $form['code']);
	$has_data = false;
	foreach ($form['codes'] as $code) {
		$sql = 'SELECT participations.participation_id, status_category_id, contact_id
				, contacts.identifier
				, invitations.parameters
				, participations.event_id
				, IF(forms.address = "informal", 1, NULL) AS informal_address
			FROM participations
			LEFT JOIN forms
				ON forms.event_id = participations.event_id
			LEFT JOIN contacts USING (contact_id)
			LEFT JOIN invitations
				ON invitations.event_id = participations.event_id
				AND invitations.usergroup_id = participations.usergroup_id
			LEFT JOIN events
				ON invitations.event_id = events.event_id
			WHERE participations.verification_hash = "%s"
			AND ISNULL(events.date_end)';
		$sql = sprintf($sql, wrap_db_escape($code));
		$data = wrap_db_fetch($sql);
		if (!$data) continue;
		wrap_setting('log_username', $data['identifier']);
		if ($data['informal_address'])
			wrap_setting('language_variation', 'informal');

		$has_data = true;
		if ($data['status_category_id'] === wrap_category_id('participation-status/subscribed')) {
			switch ($action) {
			case 'confirm':
				$error_msg = wrap_text(
					'The registration for code %s was not completed.', ['values' => [$code]]
				);
				// add activities
				$line = [
					'participation_id' => $data['participation_id'],
					'activity_category_id' => wrap_category_id('activities/verify')
				];
				zzform_insert('activities', $line, E_USER_ERROR, ['msg' => $error_msg]);

				// update participations
				$line = [
					'participation_id' => $data['participation_id'],
					'date_begin' => date('Y-m-d'),
					'status_category_id' => wrap_category_id('participation-status/verified')
				];
				zzform_update('participations', $line, E_USER_ERROR, ['msg' => $error_msg]);
				
				wrap_include_files('zzform/formkit', 'activities');
				mf_activities_formkit_mail_send($data['event_id'], $data['contact_id'], 'confirmation');
				
				$old_contact_id = mf_activities_merge_contact($data['contact_id']);
				if ($old_contact_id) $data['contact_id'] = $old_contact_id;
				break;
			case 'delete':
				// delete participation + activities (via CASCADE)
				$deleted = zzform_delete('participations', $data['participation_id']);
				if (!$deleted)
					wrap_error(sprintf('The registration for code %s was not deleted.', $code), E_USER_ERROR);
				
				// delete contact and contactdetails (if there's no other link)
				// @todo add check before
				zzform_delete('contacts', $data['contact_id']);
				// errors don’t matter
				break;
			}
			$form[$action] = true;

		} else {
			if ($action === 'confirm') {
				$form['already_confirmed'] = true;
			} elseif ($action === 'delete') {
				$form['confirmed_delete'] = true;
			} else {
				$form['form'] = true;
			}
		}
	}
	
	if (!$has_data) {
		$form['no_data'] = true;
		$form['form'] = true;
	}

	// addlogin?
	$data['parameters'] = $data['parameters'] ?? '';
	parse_str($data['parameters'], $data['parameters']);
	if (!empty($data['parameters']['addlogin'])) {
		$form['addlogin'] = true;
		$sql = 'SELECT COUNT(*) FROM logins
			WHERE contact_id = %d';
		$sql = sprintf($sql, $data['contact_id']);
		$has_logins = wrap_db_fetch($sql, '', 'single value');
		if (!$has_logins) {
			return brick_format('%%% forms addlogin '.$data['contact_id'].' url_self='.wrap_setting('request_uri').' query_strings[]=confirm %%%');
		}
	}
	
	$page['text'] = wrap_template('registration-confirmation', $form);
	return $page;
}
