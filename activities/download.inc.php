<?php 

/**
 * activities module
 * download functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * add folder(s) to media, if zip_folder = 1
 *
 * @param array $media
 * @param int $event_id
 * @return array
 */
function mf_activities_media_zip_folders($media, $event_id) {
	$sql = 'SELECT formfield_id, formfield, formfields.parameters
			, categories.category
			, categories.parameters AS category_parameters
		FROM formfields
		LEFT JOIN forms USING (form_id)
		LEFT JOIN categories
			ON categories.category_id = formfields.formfield_category_id
		WHERE event_id = %d
		AND formfields.parameters LIKE "%%&zip_folder=1%%"
		ORDER BY formfields.sequence';
	$sql = sprintf($sql, $event_id);
	$folders = wrap_db_fetch($sql, 'formfield_id');
	if (!$folders) return $media;

	foreach ($folders as $formfield_id => $folder) {
		if (!$folder['category_parameters']) {
			wrap_error(sprintf(
				'Formfield “%s” used as ZIP folder, but category “%s” does not allow this.'
				, $folder['formfield'], $folder['category']
			));
			unset($folders[$formfield_id]);
			continue;
		}
		parse_str($folder['category_parameters'], $folder['category_parameters']);
		if (empty($folder['category_parameters']['zip_folder'])) {
			wrap_error(sprintf(
				'Formfield “%s” used as ZIP folder, but category “%s” does not allow this.'
				, $folder['formfield'], $folder['category']
			));
			unset($folders[$formfield_id]);
			continue;
		}
		$sql = 'SELECT contact_id, %s
			FROM %s
			WHERE %s = %d';
		$sql = sprintf($sql
			, $folder['category_parameters']['db_field']
			, substr($folder['category_parameters']['db_field'], 0, strpos($folder['category_parameters']['db_field'], '.'))
			, $folder['category_parameters']['db_foreign_key']
			, $folder['formfield_id']
		);
		$folder['data'] = wrap_db_fetch($sql, 'contact_id', 'key/value');
		foreach ($media as $medium_id => $medium) {
			$media[$medium_id]['folders'][] = $folder['data'][$medium['contact_id']] ?? wrap_text('Unknown');
		}
	}
	// create folder, as is, but remove/replace unusable characters
	foreach ($media as $medium_id => $medium) {
		if (empty($medium['folders'])) continue;
		foreach ($medium['folders'] as $key => $folder) {
			$medium['folders'][$key] = wrap_filename($folder, ' ');
		}
		$media[$medium_id]['folder'] = implode('/', $medium['folders']);
	}
	return $media;
}
