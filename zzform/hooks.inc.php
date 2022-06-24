<?php 

/**
 * activities module
 * hooks for tables
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2022 Gustaf Mossakowski
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
		, $zz_conf['translations_table']
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
