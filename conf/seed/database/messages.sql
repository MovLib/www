-- ---------------------------------------------------------------------------------------------------------------------
-- This file is part of {@link https://github.com/MovLib MovLib}.
--
-- Copyright © 2013-present {@link https://movlib.org/ MovLib}.
--
-- MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
-- License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
-- version.
--
-- MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
-- of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License along with MovLib.
-- If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
-- ---------------------------------------------------------------------------------------------------------------------

-- ---------------------------------------------------------------------------------------------------------------------
-- Messages seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `messages`;

INSERT INTO `messages` (`message`, `dyn_translations`) VALUES
('{0} ({1})', COLUMN_CREATE('de', '{0} ({1})')),
('{0}:', COLUMN_CREATE('de', '{0}:')),
('{0}, {1}', COLUMN_CREATE('de', '{0}, {1}')),
('“{0}”', COLUMN_CREATE('de', '„{0}”')),
('the free movie library', COLUMN_CREATE('de', 'die freie Kinemathek')),
('{0}, the free movie library.', COLUMN_CREATE('de', '{0}, die freie Kinemathek.')),
('{0} {1}the {2}free{3} movie library.{4}', COLUMN_CREATE('de', '{0} {1}die {2}freie{3} Kinemathek.{4}')),
('Go back to the home page.', COLUMN_CREATE('de', 'Gehe zurück zur Startseite.')),
('{page_title} — {sitename}', COLUMN_CREATE('de', '{page_title} – {sitename}')),
('IP address or user agent string is invalid or empty.', COLUMN_CREATE('de', 'IP-Adresse oder User-Agent-Zeichenkette ist invalide oder leer.')),
('Please note that you have to submit your IP address and user agent string to identify yourself as being human; should you have privacy concerns read our {privacy_policy}.', COLUMN_CREATE('de', 'Bitte nimm zur Kenntniss, dass du deine IP-Adresse und User-Agent-Zeichenkette übermitteln musst um dich als Mensch zu identifizieren; sollte du Datenschutzbedenken haben lies unsere {privacy_policy}.')),
('You’re currently viewing this page.', COLUMN_CREATE('de', 'Du betrachtest diese Seite momentan.')),
('Choose your language', COLUMN_CREATE('de', 'Wähle deine Sprache')),
('Language', COLUMN_CREATE('de', 'Sprache')),
('Infos all around {sitename}', COLUMN_CREATE('de', 'Information rund um {sitename}')),
('Copyright and licensing information', COLUMN_CREATE('de', 'Urheber- und Lizenzinformationen')),
('Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.', COLUMN_CREATE('de', 'Datenbankdaten sind unter der {0}Creative Commons — CC0 1.0 Universal{1} Lizenz verfügbar.')),
('Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated.', COLUMN_CREATE('de', 'Inhalte von Dritten unterliegen möglicherweise zusätzlichen Bedingungen, bitte achte auf zusätzliche Lizenz- und Urheberinformatinen.')),
('Sponsors and external resources', COLUMN_CREATE('de', 'Sponsoren und externe Ressourcen')),
('Made with {love} in Austria', COLUMN_CREATE('de', 'Hergestellt mit {love} in Österreich')),
('love', COLUMN_CREATE('de', 'Liebe')),
('Legal Links', COLUMN_CREATE('de', 'Rechtliche Links')),
('Privacy Policy', COLUMN_CREATE('de', 'Datenschutz')),
('Terms of Use', COLUMN_CREATE('de', 'Nutzungsbedingungen')),
('Save', COLUMN_CREATE('de', 'Speichern')),
('Change', COLUMN_CREATE('de', 'Ändern')),
('Reset', COLUMN_CREATE('de', 'Zurücksetzen')),
('Home', COLUMN_CREATE('de', 'Startseite')),
('Movie', COLUMN_CREATE('de', 'Film')),
('Movies', COLUMN_CREATE('de', 'Filme')),
('Release', COLUMN_CREATE('de', 'Veröffentlichung')),
('Releases', COLUMN_CREATE('de', 'Veröffentlichungen')),
('Persons', COLUMN_CREATE('de', 'Personen')),
('Series', COLUMN_CREATE('de', 'Serien')),
('Company', COLUMN_CREATE('de', 'Unternehmen')),
('Companies', COLUMN_CREATE('de', 'Unternehmen')),
('Help', COLUMN_CREATE('de', 'Hilfe')),
('Create New Movie', COLUMN_CREATE('de', 'Neuen Film Anlegen')),
('Create New Series', COLUMN_CREATE('de', 'Neue Serie Anlegen')),
('Create New Release', COLUMN_CREATE('de', 'Neue Veröffentlichung Anlegen')),
('Create New Person', COLUMN_CREATE('de', 'Neue Person Anlegen')),
('Create New Company', COLUMN_CREATE('de', 'Neues Unternehmen Anlegen')),
('Create New Award', COLUMN_CREATE('de', 'Neue Auszeichnung Anlegen')),
('Create New Genre', COLUMN_CREATE('de', 'Neues Genre Anlegen')),
('Create New Job', COLUMN_CREATE('de', 'Neue Tätigkeit Anlegen')),
('Latest Entries', COLUMN_CREATE('de', 'Neueste Einträge')),
('Charts', COLUMN_CREATE('de', 'Charts')),
('Profile', COLUMN_CREATE('de', 'Profil')),
('Messages', COLUMN_CREATE('de', 'Nachrichten')),
('Collection', COLUMN_CREATE('de', 'Sammlung')),
('Wantlist', COLUMN_CREATE('de', 'Wunschliste')),
('Lists', COLUMN_CREATE('de', 'Listen')),
('Watchlist', COLUMN_CREATE('de', 'Beobachtungsliste')),
('Account', COLUMN_CREATE('de', 'Konto')),
('Notifications', COLUMN_CREATE('de', 'Benachrichtungen')),
('Email', COLUMN_CREATE('de', 'E-Mail')),
('Email Address', COLUMN_CREATE('de', 'E-Mail-Adresse')),
('Password', COLUMN_CREATE('de', 'Passwort')),
('Danger Zone', COLUMN_CREATE('de', 'Gefahrenzone')),
('Sign In', COLUMN_CREATE('de', 'Anmelden')),
('User', COLUMN_CREATE('de', 'Benutzer')),
('Users', COLUMN_CREATE('de', 'Benutzer')),
('Your profile is currently empty, {0}click here to edit{1}.', COLUMN_CREATE('de', 'Dein Profil ist derzeit leer, {0}klicke hier um es zu bearbeiten{1}.')),
('Joined {date} and was last seen {time}.', COLUMN_CREATE('de', 'Beigetreten am {date} und wurde zuletzt gesehen: {time}.')),
('Recently Rated Movies', COLUMN_CREATE('de', 'Zuletzt Bewertete Filme')),
('Runtime', COLUMN_CREATE('de', 'Laufzeit')),
('Join', COLUMN_CREATE('de', 'Beitreten')),
('Subject', COLUMN_CREATE('de', 'Betreff')),
('Send', COLUMN_CREATE('de', 'Senden')),
('This will appear as subject of your message', COLUMN_CREATE('de', 'Dies wird der Betreff deiner Nachricht')),
('Enter “{0}” text here …', COLUMN_CREATE('de', 'Gib deinen „{0}” Text hier ein …')),
('original title', COLUMN_CREATE('de', 'Originaltitel')),
('born name', COLUMN_CREATE('de', 'Geburtsname')),
('No jobs available. Please go to a movie or series page and add them there.', COLUMN_CREATE('de', 'Keine Jobs vorhanden. Bitte gehe zu einer Film- oder Serienseite und füge diese dort hinzu.')),
('Born on {date} in {place} and would be {age} years old.', COLUMN_CREATE('de', 'Geboren am {date} in {place} und würde heute {age} Jahre alt sein.')),
('Born on {date} in {place} and is {age} years old.', COLUMN_CREATE('de', 'Geboren am {date} in {place} und ist heute {age} Jahre alt.')),
('Born on {date} and would be {age} years old.', COLUMN_CREATE('de', 'Geboren am {date} und würde heute {age} Jahre alt sein.')),
('Born on {date} and is {age} years old.', COLUMN_CREATE('de', 'Geboren am {date} und ist heute {age} Jahre alt.')),
('Born in {place}.', COLUMN_CREATE('de', 'Geboren in {place}.')),
('Died on {date} in {place} at the age of {age} years.', COLUMN_CREATE('de', 'Gestorben am {date} in {place} im Alter von {age} Jahren.')),
('Died on {date} in {place}.', COLUMN_CREATE('de', 'Gestorben am {date} in {place}.')),
('Died on {date} at the age of {age} years.', COLUMN_CREATE('de', 'Gestorben am {date} im Alter von {age} Jahren.')),
('Died on {date}.', COLUMN_CREATE('de', 'Gestorben am {date}.')),
('Died in {place}.', COLUMN_CREATE('de', 'Gestorben in {place}.')),
('Wikipedia Article', COLUMN_CREATE('de', 'Wikipedia-Artikel')),
('Join {sitename}', COLUMN_CREATE('de', '{sitename} beitreten')),
('Reset Password', COLUMN_CREATE('de', 'Passwort Zurücksetzen')),
('Forgot Password', COLUMN_CREATE('de', 'Passwort Vergessen')),
('Forgot your password?', COLUMN_CREATE('de', 'Passwort vergessen?')),
('You must be signed in to access this content.', COLUMN_CREATE('de', 'Du musst angemeldet sein um auf diesen Inhalt zugreifen zu können.')),
('Please sign in again to verify the legitimacy of this request.', COLUMN_CREATE('de', 'Bitte melde dich erneut an um die Legitimität dieser Anfrage zu bestätigen.')),
('Active Sessions', COLUMN_CREATE('de', 'Aktive Sessions')),
('Delete Account', COLUMN_CREATE('de', 'Konto Löschen')),
('Delete', COLUMN_CREATE('de', 'Löschen')),
('Sign In Time', COLUMN_CREATE('de', 'Anmeldezeit')),
('User Agent', COLUMN_CREATE('de', 'User-Agent')),
('IP address', COLUMN_CREATE('de', 'IP-Adresse')),
('Password Settings', COLUMN_CREATE('de', 'Passworteinstellungen')),
('Email Settings', COLUMN_CREATE('de', 'E-Mail-Einstellungen')),
('Your current email address is {0}', COLUMN_CREATE('de', 'Deine aktuelle E-Mail-Adresse ist {0}')),
('Enter your email address', COLUMN_CREATE('de', 'Gib deine E-Mail-Adresse ein')),
('New Password', COLUMN_CREATE('de', 'Neues Passwort')),
('Confirm Password', COLUMN_CREATE('de', 'Bestätigunspasswort')),
('Enter your new password', COLUMN_CREATE('de', 'Gib dein neues Passwort ein')),
('Enter your new password again', COLUMN_CREATE('de', 'Gib dein neues Passwort erneut ein')),
('Notification Settings', COLUMN_CREATE('de', 'Benachrichtigungseinstellungen')),
('Check back later', COLUMN_CREATE('de', 'Schau später nochmals vorbei')),
('Account Settings', COLUMN_CREATE('de', 'Kontoeinstellungen')),
('Real Name', COLUMN_CREATE('de', 'Bürgerlicher Name')),
('Avatar', COLUMN_CREATE('de', 'Avatar')),
('Sex', COLUMN_CREATE('de', 'Geschlecht')),
('Female', COLUMN_CREATE('de', 'Weiblich')),
('Male', COLUMN_CREATE('de', 'Männlich')),
('Unknown', COLUMN_CREATE('de', 'Unbekannt')),
('Date of Birth', COLUMN_CREATE('de', 'Geburtsdatum')),
('About Me', COLUMN_CREATE('de', 'Über Mich')),
('System Language', COLUMN_CREATE('de', 'Systemsprache')),
('Country', COLUMN_CREATE('de', 'Land')),
('Time Zone', COLUMN_CREATE('de', 'Zeitzone')),
('Currency', COLUMN_CREATE('de', 'Währung')),
('Website', COLUMN_CREATE('de', 'Website')),
('Keep my data private!', COLUMN_CREATE('de', 'Halte meine Daten privat!')),
('Tip', COLUMN_CREATE('de', 'Tipp')),
('{image_name} {current} of {total} from {title}', COLUMN_CREATE('de', '{image_name} {current} von {total} von {title}')),
('Explore', COLUMN_CREATE('de', 'Entdecken')),
('Marketplace', COLUMN_CREATE('de', 'Marktplatz')),
('My Messages', COLUMN_CREATE('de', 'Meine Nachrichten')),
('My Collection', COLUMN_CREATE('de', 'Meine Sammlung')),
('My Wantlist', COLUMN_CREATE('de', 'Meine Wunschliste')),
('My Lists', COLUMN_CREATE('de', 'Meine Listen')),
('My Watchlist', COLUMN_CREATE('de', 'Meine Beobachtungsliste')),
('Settings', COLUMN_CREATE('de', 'Einstellungen')),
('Sign Out', COLUMN_CREATE('de', 'Abmelden')),
('My', COLUMN_CREATE('de', 'Mein')),
('Latest Users', COLUMN_CREATE('de', 'Neueste Benutzer')),
('Utilities', COLUMN_CREATE('de', 'Werkzeuge')),
('Deletion Requests', COLUMN_CREATE('de', 'Löschanträge')),
('Create New', COLUMN_CREATE('de', 'Neu Anlegen')),
('Random Movie', COLUMN_CREATE('de', 'Zufälliger Film')),
('Random Series', COLUMN_CREATE('de', 'Zufällige Serie')),
('Random Person', COLUMN_CREATE('de', 'Zufällige Person')),
('Random Company', COLUMN_CREATE('de', 'Zufälliges Unternehmen')),
('More', COLUMN_CREATE('de', 'Mehr')),
('Explore all genres', COLUMN_CREATE('de', 'Entdecke alle Genres')),
('Explore all articles', COLUMN_CREATE('de', 'Entdecke alle Artikel')),
('Do you like movies?{0}Great, so do we!', COLUMN_CREATE('de', 'Du magst Filme?{0}Großartig, wir auch!')),
('My {sitename}', COLUMN_CREATE('de', 'Mein {sitename}')),
('Results from {from,number,integer} to {to,number,integer} of {total,number,integer} results.', COLUMN_CREATE('de', 'Ergebnisse von {from,number,integer} bis {to,number,integer} von {total,number,integer} Ergebnissen.')),
('View', COLUMN_CREATE('de', 'Ansehen')),
('Edit', COLUMN_CREATE('de', 'Bearbeiten')),
('Discuss', COLUMN_CREATE('de', 'Diskutieren')),
('History', COLUMN_CREATE('de', 'Geschichte')),
('Synopsis', COLUMN_CREATE('de', 'Synopsis')),
('Cast', COLUMN_CREATE('de', 'Darsteller')),
('Directors', COLUMN_CREATE('de', 'Regisseure')),
('Trailers', COLUMN_CREATE('de', 'Trailer')),
('Reviews', COLUMN_CREATE('de', 'Rezensionen')),
('No countries assigned yet, {0}add countries{1}?', COLUMN_CREATE('de', 'Keine Länder zugeordnet, {0}Länder hinzufügen{1}?')),
('No genres assigned yet, {0}add genres{1}?', COLUMN_CREATE('de', 'Keine Genres zugeordnet, {0}Genres hinzufügen{1}?')),
('No synopsis available, {0}write synopsis{1}?', COLUMN_CREATE('de', 'Keine Synopsis vorhanden, {0}Synopsis verfassen{1}?')),
('No directors assigned yet, {0}add directors{1}?', COLUMN_CREATE('de', 'Keine Regisseure zugeordnet, {0}Regisseure hinzufügen{1}?')),
('No cast assigned yet, {0}add cast{1}?', COLUMN_CREATE('de', 'Keine Darsteller zugeordnet, {0}Darsteller hinzufügen{1}?')),
('Awful', COLUMN_CREATE('de', 'Furchtbar')),
('Bad', COLUMN_CREATE('de', 'Schlecht')),
('Okay', COLUMN_CREATE('de', 'OK')),
('Fine', COLUMN_CREATE('de', 'Gut')),
('Awesome', COLUMN_CREATE('de', 'Großartig')),
('with {0, plural, one {one star} other {# stars}}', COLUMN_CREATE('de', 'mit {0, plural, one {einem Stern} other {# Sternen}}')),
('You’re the only one who voted for this movie (yet).', COLUMN_CREATE('de', 'Nur du hast diesen Film (bisher) bewertet.')),
('No one has rated this movie so far, be the first.', COLUMN_CREATE('de', 'Niemand hat diesen Film bisher bewertet, sei der Erste.')),
('You’re the only one who rated this movie (yet).', COLUMN_CREATE('de', 'Nur du hast diesen Film (bisher) bewertet.')),
('Rated by {votes} user with {rating}.', COLUMN_CREATE('de', 'Bewertet von {votes} Benutzer mit {rating}.')),
('Rated by {votes} users with a {0}mean rating{1} of {rating}.', COLUMN_CREATE('de', 'Bewertet von {votes} Benutzern mit einer {0}Durchschnittsbewertung{1} von {rating}.')),
('View the rating demographics.', COLUMN_CREATE('de', 'Bewertungsdemographien ansehen.')),
('Rate this movie', COLUMN_CREATE('de', 'Diesen Film bewerten')),
('Please {sign_in} or {join} to rate this movie.', COLUMN_CREATE('de', 'Bitte {sign_in} oder {join} um diesen Film zu bewerten.')),
('The submitted rating isn’t valid. Valid ratings range from: {min} to {max}', COLUMN_CREATE('de', 'Die übermittelte Bewertung ist nicht valide. Valide Bewertung sind von {min} bis {max}.')),
('Enter the email address associated with your {sitename} account. Password reset instructions will be sent via email.', COLUMN_CREATE('de', 'Gib die E-Mail-Adresse deines {sitename}-Kontos ein. Instruktionen zum Zurücksetzen deines Passworts werden via E-Mail versandt.')),
('We hope to see you again soon.', COLUMN_CREATE('de', 'Wir hoffen dich bald wieder zu sehen.')),
('Sign Out Successfull', COLUMN_CREATE('de', 'Abmeldung Erfolgreich')),
('Enter your password', COLUMN_CREATE('de', 'Gib dein Passwort ein')),
('Sign Up', COLUMN_CREATE('de', 'Beitreten')),
('Username', COLUMN_CREATE('de', 'Benutzername')),
('Enter your desired username', COLUMN_CREATE('de', 'Gib deinen gewünschten Benutzernamen ein')),
('I accept the {privacy_policy} and {terms_of_use}.', COLUMN_CREATE('de', 'Ich akzeptiere die {privacy_policy} und {terms_of_use}.')),
('A username must be valid UTF-8, cannot contain spaces at the beginning and end or more than one space in a row, it cannot contain any of the following characters {0} and it cannot be longer than {1,number,integer} characters.', COLUMN_CREATE('de', 'Ein Benutzername muss in validem UTF-8 sein, kann keine Leerzeichen am Anfang und Ende oder mehr als eines hintereinander besitzen, weiters kann er keine dieser Zeichen {0} beinhalten und nicht länger als {1,number,integer} Zeichen sein.')),
('An email address in the format [local]@[host].[tld] with a maximum of {0,number,integer} characters', COLUMN_CREATE('de', 'Eine E-Mail-Adresse im Format [local]@[host].[tld] mit maximal {0,number,integer} Zeichen.')),
('Is your language missing in our list? {0}Help us translate {sitename}.{1}', COLUMN_CREATE('de', 'Fehlt deine Sprache in unserer Liste? {0}Hilf uns {sitename} zu übersetzen.{1}')),
('Please select your preferred language from the following list.', COLUMN_CREATE('de', 'Bitte wähle deine bevorzugte Sprache aus der folgenden Liste.')),
('Is your language missing from our list? Help us translate {sitename} to your language. More information can be found at {0}our translation portal{1}.', COLUMN_CREATE('de', 'Fehlt deine Sprache in unserer Liste? Hilf uns {sitename} in deine Sprache zu übersetzen. Mehr Informationen findest du in {0}unserem Übersetzungsportal{1}.')),
('Internal Server Error', COLUMN_CREATE('de', 'Interner Serverfehler')),
('An unexpected condition which prevented us from fulfilling the request was encountered.', COLUMN_CREATE('de', 'Ein unerwarteter Zustand hat uns davon abgehalten deine Anfrage zu erfüllen.')),
('This error was reported to the system administrators, it should be fixed in no time. Please try again in a few minutes.', COLUMN_CREATE('de', 'Dieser Fehler wurde an die System-Administratoren gemeldet und sollte schnell repariert werden. Bitte probiere es in ein paar Minuten erneut.')),
('Stacktrace for {0}', COLUMN_CREATE('de', 'Stacktrace für {0}')),
('Forbidden', COLUMN_CREATE('de', 'Untersagt')),
('Access to the requested page is forbidden.', COLUMN_CREATE('de', 'Zugriff auf die angeforderte Seite ist untersagt.')),
('Only administrators can handle deletion requests.', COLUMN_CREATE('de', 'Ausschließlich Administratoren können Löschanträge bearbeiten.')),
('All', COLUMN_CREATE('de', 'Alle')),
('Spam', COLUMN_CREATE('de', 'Spam')),
('Duplicate', COLUMN_CREATE('de', 'Duplikate')),
('Other', COLUMN_CREATE('de', 'Andere')),
('Great, not a single deletion request is waiting for approval.', COLUMN_CREATE('de', 'Gorßartig, nicht ein einziger Löschantrag wartet auf Bearbeitung.')),
('No Deletion Requests', COLUMN_CREATE('de', 'Keine Löschanträge')),
('{date}: {user} has requested that {0}this content{1} should be deleted.', COLUMN_CREATE('de', '{date}: {user} hat angefordert, dass {0}dieser Inhalt{1} gelöscht werden sollte.')),
('{date}: {user} has requested that {0}this content{1} should be deleted for the reason: “{reason}”', COLUMN_CREATE('de', '{date}: {user} hat angefordert, dass {0}dieser Inhalt{1} gelöscht werden sollte aus dem Grund: „{reason}”')),
('You can filter the deletion requests via the sidebar menu.', COLUMN_CREATE('de', 'Du kannst die Löschanträge über das seitliche Navigationsmenü filtern.')),
('Successfully Signed In', COLUMN_CREATE('de', 'Erfolgreich Angemeldet')),
('Welcome back {username}!', COLUMN_CREATE('de', 'Willkommen zurück {username}!')),
('Not Found', COLUMN_CREATE('de', 'Nicht Gefunden')),
('The requested page could not be found.', COLUMN_CREATE('de', 'Die angeforderte Seite konnte nicht gefunden werden.')),
('There can be various reasons why you might see this error message. If you feel that receiving this error is a mistake please {0}contact us{1}.', COLUMN_CREATE('de', 'Es kann viele verschiedene Gründe für diese Fehlermeldung geben. Solltest du der Meinung sein, dass dieser Fehler nicht korrekt ist {0}kontaktiere uns{1}.')),
('{image_name} for {title}', COLUMN_CREATE('de', '{image_name} für {title}')),
('Poster', COLUMN_CREATE('de', 'Poster')),
('Posters', COLUMN_CREATE('de', 'Poster')),
('Lobby Card', COLUMN_CREATE('de', 'Aushangbild')),
('Lobby Cards', COLUMN_CREATE('de', 'Aushangbilder')),
('Backdrop', COLUMN_CREATE('de', 'Hintergrund')),
('Backdrops', COLUMN_CREATE('de', 'Hintegründe')),
('Description', COLUMN_CREATE('de', 'Beschreibung')),
('Publishing Date', COLUMN_CREATE('de', 'Veröffentlichungsdatum')),
('Upload', COLUMN_CREATE('de', 'Hochladen')),
('Upload new poster for {title}', COLUMN_CREATE('de', 'Neues Poster für {title} hochladen')),
('Upload new lobby card for {title}', COLUMN_CREATE('de', 'Neues Aushangbild für {title} hochladen')),
('Upload new backdrop for {title}', COLUMN_CREATE('de', 'Neuen Hintergrund für {title} hochladen')),
('None', COLUMN_CREATE('de', 'Keines')),
('No Language', COLUMN_CREATE('de', 'Keine Sprache')),
('Please Select …', COLUMN_CREATE('de', 'Bitte auswählen …')),
('The image you see is only a preview, you still have to submit the form.', COLUMN_CREATE('de', 'Das Bild das du siehst ist ausschließlich eine Vorschau, du musst das Formular absenden um es hochzuladen.')),
('Provided by', COLUMN_CREATE('de', 'Bereitgestellt von')),
('File size', COLUMN_CREATE('de', 'Dateigröße')),
('Upload on', COLUMN_CREATE('de', 'Hochgeladen am')),
('Dimensions', COLUMN_CREATE('de', 'Dimensionen')),
('Upload New', COLUMN_CREATE('de', 'Neues Hochladen')),
('Back to movie', COLUMN_CREATE('de', 'Zurück zum Film')),
('Successfully Edited', COLUMN_CREATE('de', 'Erfolgreich Bearbeitet')),
('previous', COLUMN_CREATE('de', 'zurück')),
('next', COLUMN_CREATE('de', 'weiter')),
('No Data Available', COLUMN_CREATE('de', 'Keine Daten Verfügbar')),
('{sitename} has no further details about {person_name}.', COLUMN_CREATE('de', '{sitename} hat keine weiteren Details zu {person_name}.')),
('Biography', COLUMN_CREATE('de', 'Biographie')),
('Filmography', COLUMN_CREATE('de', 'Filmographie')),
('Director', COLUMN_CREATE('de', 'Regisseur')),
('Also Known As', COLUMN_CREATE('de', 'Auch Bekannt Als')),
('External Links', COLUMN_CREATE('de', 'Externe Links')),
('Jobs', COLUMN_CREATE('de', 'Tätigkeiten')),
('Job', COLUMN_CREATE('de', 'Tätigkeit')),
('Awards', COLUMN_CREATE('de', 'Auszeichnungen')),
('Award', COLUMN_CREATE('de', 'Auszeichnung')),
('Discussion', COLUMN_CREATE('de', 'Diskussion')),
('Discussion of {0}', COLUMN_CREATE('de', 'Diskussion über {0}')),
('Edit {0}', COLUMN_CREATE('de', '{0} bearbeiten')),
('History of {0}', COLUMN_CREATE('de', 'Geschichte von {0}')),
('Delete {0}', COLUMN_CREATE('de', '{0} löschen')),
('Movies with {0}', COLUMN_CREATE('de', 'Filme mit {0}')),
('Movies from {0}', COLUMN_CREATE('de', 'Filme von {0}')),
('Series with {0}', COLUMN_CREATE('de', 'Serien mit {0}')),
('Series from {0}', COLUMN_CREATE('de', 'Serien von {0}')),
('Releases with {0}', COLUMN_CREATE('de', 'Veröffentlichungen mit {0}')),
('Releases from {0}', COLUMN_CREATE('de', 'Veröffentlichungen von {0}')),
('from {0} to {1}', COLUMN_CREATE('de', 'von {0} bis {1}')),
('since {0}', COLUMN_CREATE('de', 'seit {0}')),
('until {0}', COLUMN_CREATE('de', 'bis {0}')),

-- Coming Soon Page (must be last)

('Sign up for the {sitename} beta!', COLUMN_CREATE('de', 'Melde dich jetzt für die {sitename} Beta an!')),
('The open beta is scheduled to start in June 2014.', COLUMN_CREATE('de', 'Die offene Beta ist für den Juni 2014 geplant.')),
('Wanna see the current alpha version of {sitename}? Go to {alpha_url}', COLUMN_CREATE('de', 'Willst du die aktuelle Alpha-Version von {sitename} sehen? Gehe zu {alpha_url}')),
('Imagine {1}Wikipedia{0}, {2}Discogs{0}, {3}Last.fm{0}, {4}IMDb{0}, and {5}TheMovieDB{0} combined in a totally free and open project.', COLUMN_CREATE('de', 'Stell dir {1}Wikipedia{0}, {2}Discogs{0}, {3}Last.fm{0}, {4}IMDb{0} und {5}TheMovieDB{0} kombiniert in einem völlig freien und offenen Projekt vor.')),
('Thanks for signing up for the {sitename} beta {email}.', COLUMN_CREATE('de', 'Danke für deine Anmeldung zur {sitename} Beta {email}.')),
('Successfully Signed Up', COLUMN_CREATE('de', 'Erfolgreich Angemeldet'));
