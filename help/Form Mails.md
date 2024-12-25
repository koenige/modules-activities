<!--
# activities module
# about form mails
#
# Part of »Zugzwang Project«
# https://www.zugzwang.org/modules/activities
#
# @author Gustaf Mossakowski <gustaf@koenige.org>
# @copyright Copyright © 2024 Gustaf Mossakowski
# @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
#
-->

# Form Mails

After a form was submitted by the registrant, an authentication e-mail
can be sent automatically to the registrant, ensuring that it was her or
him who made the registration. The registrant needs to get at least an
authentication link where she can authenticate her e-mail address.

It is possible to send further mails, if configured:

– A confirmation e-mail to confirm, that the registration was
authenticated and will be processed further internally.

– An e-mail which will be send if a field value was changed by the
registrant or organizer. This only works for registration with login,
where entries can be changed multiple times during the registration
process.

The e-mails can contain placeholders for entering the registrant‘s data
or information about the event. If you use double opt-in, it is
necessary to include at least the `authentication_link`.

## Placeholders in Form Mails

### Registrant Data

- `%%% item contact %%%` – Inserts full name of registrant

- `%%% item first_name %%%` – Inserts only first name of registrant

- `%%% item last_name %%%` – Inserts only last name of registrant

- `%%% if male %%%` … `%%% endif %%%` – Condition
that shows content only if registrant is male, e. g. to address a
person. `female`, `diverse` and `unknown` are available, too. Instead of
an `endif`, you can also combine several of these conditions with an
`elseif`. Examples: 
`%%% if female %%%` … `%%% endif %%%` 
`%%% if diverse %%%` … `%%% endif %%%` 
`%%% if unknown %%%` … `%%% endif %%%` 
`%%% if male %%%` … `%%% elseif fmale %%%` … 
`%%% endif %%%`

- `%%% item e_mail %%%` – Inserts e-mail address of registrant

- `%%% item submission %%%` – Inserts all submitted data in one block,
formatted as list `field name: value`, adding spaces after the field
name so that all value are aligned.

- `%%% item fieldtitle %%%` – Inserts the title of a changed field.
(Only available for e-mails sent after field values have been changed.)

- `%%% item fieldvalue %%%` – Inserts the value of a changed field.
(Only available for e-mails sent after field values have been changed.)

### Event Data

- `%%% item event %%%` – Inserts the name of the event

- `%%% item identifier %%%` – Inserts Identifier of the event (part of
the URL)

- `%%% item duration %%%` – Inserts duration of the event, formatted
according to language settings

- `%%% if formal_address %%%` … `%%% endif %%%` –
Show content only if the form addresses registrants formally.

- `%%% if informal_address %%%` … `%%% endif %%%` –
Show content only if the form addresses registrants informally.

- `%%% item form_category %%%` – Inserts category of form, e. g.
Registration, Booking, Application – whatever categories are defined in
the system.

### Registration Data

Data used for the double opt-in as part of the registration process.

- `%%% item authentication_link %%%` – Inserts a link that the
registrant can click to authenticate the e-mail address used for the
registration.

- `%%% item rejection_link %%%` – Inserts a link that the registrant can
click to reject the authentication of the e-mail address used for the
registration (optional).

- `%%% item verification_hash %%%` – Insert verification hash of
registrant (which is part of both the authentication and the rejection
link, i. e. it normally does not need to be included in the form mail).

## Add Custom Data

A developer can add custom data (or combine existing data in other ways)
using a custom hook function for form emails:

    my_formmail_prepare($data)

This function retrieves the existing data as an array and must return
this array with custom modifications.
