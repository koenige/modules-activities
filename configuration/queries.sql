/**
 * activities module
 * SQL queries
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/activities
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


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
