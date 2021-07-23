<?php 

/**
 * activities module
 * Confirm a registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_activities_make_registrationconfirmation() {
	global $zz_conf;
	global $zz_setting;
	global $zz_page;

	$zz_setting['cache'] = false;
	$zz_setting['extra_http_headers'][] = 'X-Frame-Options: Deny';
	$zz_setting['extra_http_headers'][] = "Content-Security-Policy: frame-ancestors 'self'";

	$form = [];
	$form['reminder'] = false;
	$url = parse_url($_SERVER['REQUEST_URI']);
	$form['action'] = $url['path'];

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
		$sql = 'SELECT participation_id, status_category_id, identifier, contact_id
				, registrations.parameters
			FROM participations
			LEFT JOIN contacts USING (contact_id)
			LEFT JOIN registrations
				ON registrations.event_id = participations.event_id
				AND registrations.usergroup_id = participations.usergroup_id
			WHERE verification_hash = "%s"
			AND ISNULL(date_end)';
		$sql = sprintf($sql, wrap_db_escape($code));
		$data = wrap_db_fetch($sql);
		if (!$data) continue;
		$zz_conf['user'] = $data['identifier'];

		$has_data = true;
		if ($data['status_category_id'] === wrap_category_id('participation-status/subscribed')) {
			require_once $zz_conf['dir'].'/zzform.php';

			switch ($action) {
			case 'confirm':
				// add activities
				$values = [];
				$values['action'] = 'insert';
				$values['ids'] = ['participation_id', 'activity_category_id'];
				$values['POST']['participation_id'] = $data['participation_id'];
				$values['POST']['activity_category_id'] = wrap_category_id('activities/verify');
				$activity = zzform_multi('activities', $values);
				if (!$activity['id'])
					wrap_error(sprintf('The registration for code %s was not completed.', $code), E_USER_ERROR);

				// update participations
				$values = [];
				$values['action'] = 'update';
				$values['ids'] = ['status_category_id'];
				$values['POST']['participation_id'] = $data['participation_id'];
				$values['POST']['date_begin'] = date('Y-m-d');
				$values['POST']['status_category_id'] = wrap_category_id('participation-status/verified');
				$participation = zzform_multi('participations', $values);
				if (!$participation['id'])
					wrap_error(sprintf('The registration for code %s was not completed.', $code), E_USER_ERROR);
				
				// @todo merge contact with contact with identical name + mail?
				break;
			case 'delete':
				// delete participation + activities (via CASCADE)
				$values = [];
				$values['action'] = 'delete';
				$values['POST']['participation_id'] = $data['participation_id'];
				$participation = zzform_multi('participations', $values);
				if (!$participation['id'])
					wrap_error(sprintf('The registration for code %s was not deleted.', $code), E_USER_ERROR);
				
				// delete contact and contactdetails (if there's no other link)
				// @todo add check before
				$values = [];
				$values['action'] = 'delete';
				$values['POST']['contact_id'] = $data['contact_id'];
				$contacts = zzform_multi('contacts', $values);
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
			return brick_format('%%% forms addlogin '.$data['contact_id'].' url_self='.$zz_setting['request_uri'].' query_strings[]=confirm %%%');
		}
	}
	
	$page['text'] = wrap_template('registration-confirmation', $form);
	return $page;
}
