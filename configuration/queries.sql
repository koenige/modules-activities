/**
 * activities module
 * SQL queries
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


-- activities_event_id --
SELECT event_id, form_id
	, (SELECT COUNT(*) FROM participations
		WHERE participations.event_id = events.event_id
		AND usergroup_id IN (0, 0)
	) AS applicants
	, IF(published = "yes", 1, NULL) AS published
FROM events
LEFT JOIN forms USING (event_id)
WHERE event_id = %d

-- activities_mailings_event --
SELECT event_id, event
, CONCAT(date_begin, IFNULL(CONCAT("/", date_end), "")) AS duration
, events.identifier AS event_identifier
, (SELECT place FROM events_contacts
	LEFT JOIN addresses USING (contact_id)
	WHERE events_contacts.event_id = events.event_id
	AND events_contacts.role_category_id = /*_ID categories roles/location */
	LIMIT 1
) AS place
FROM events
WHERE event_id = %d

-- activities_mailings_recipients --
SELECT persons.contact_id
, (SELECT identification FROM contactdetails
	WHERE contactdetails.contact_id = contacts.contact_id
	AND provider_category_id = /*_ID categories provider/e-mail */
	LIMIT 1
) AS e_mail
, contact AS name
, first_name
, CONCAT(IFNULL(CONCAT(name_particle, " "), ""), last_name) AS last_name
, contacts.identifier
, IF((SELECT COUNT(*) FROM logins WHERE logins.contact_id = persons.contact_id), 1, NULL) AS login
, IF(persons.sex = "female", 1, NULL) AS female
, IF(persons.sex = "male", 1, NULL) AS male
, IF(persons.sex = "diverse", 1, NULL) AS diverse
, IF(ISNULL(persons.sex), 1, NULL) AS sex_unknown
FROM contacts
LEFT JOIN persons USING (contact_id)
LEFT JOIN participations USING (contact_id)
WHERE contacts.contact_id IN (%s)
AND participations.event_id = %d

-- activities_organisation_contact_id --
SELECT contact_id, contact, parameters
FROM contacts
WHERE contact_id = %d

-- activities_placeholder_form --
SELECT event_id, event, identifier, form_id, abstract, events.description
	, IF(published = "yes", 1, NULL) AS published
	, IF((SELECT COUNT(*) FROM formtemplates
		WHERE formtemplates.form_id = forms.form_id
		AND template_category_id = /*_ID CATEGORIES template-types/authentication _*/)
	>= 1, NULL, IF(form_categories.parameters LIKE "%%&no_authentication_mail=1%%", NULL, 1
	)) AS formtemplates_authentication_missing
	, IF((SELECT COUNT(*) FROM formtemplates
		WHERE formtemplates.form_id = forms.form_id
		AND template_category_id = /*_ID CATEGORIES template-types/confirmation _*/)
	>= 1, NULL, IF(form_categories.parameters LIKE "%%&no_confirmation_mail=1%%", NULL, 1
	)) AS formtemplates_confirmation_missing
	, (SELECT GROUP_CONCAT(formfield_category_id SEPARATOR ",") FROM formfields
		WHERE formfields.form_id = forms.form_id) AS formfield_category_ids
	, forms.access
	, form_categories.category_id
	, form_categories.category
	, form_categories.parameters AS form_parameters
	, forms.form_id, forms.lead, forms.header, forms.footer
FROM events
LEFT JOIN forms USING (event_id)
LEFT JOIN websites USING (website_id)
LEFT JOIN categories form_categories
	ON forms.form_category_id = form_categories.category_id
WHERE identifier = "%s"
AND event_category_id = %d
AND website_id = %d;

-- activities_website_id --
/* ignore */
