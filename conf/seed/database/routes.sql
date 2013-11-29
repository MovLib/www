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
-- Routes seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO `routes` (`route`, `dyn_translations`) VALUES

-- Movie(s)
('/movies',               COLUMN_CREATE('de', '/filme')),
('/movie',                COLUMN_CREATE('de', '/film')),
('/movie/create',         COLUMN_CREATE('de', '/film/erstellen')),
('/movie/{0}',            COLUMN_CREATE('de', '/film/{0}')),
('/movie/{0}/discussion', COLUMN_CREATE('de', '/film/{0}/diskussion')),
('/movie/{0}/edit',       COLUMN_CREATE('de', '/film/{0}/bearbeiten')),
('/movie/{0}/delete',     COLUMN_CREATE('de', '/film/{0}/löschen')),

-- Movie Poster(s)
('/movie/{0}/posters',           COLUMN_CREATE('de', '/film/{0}/poster')),
('/movie/{0}/poster/upload',     COLUMN_CREATE('de', '/film/{0}/poster/hochladen')),
('/movie/{0}/poster/{1}',        COLUMN_CREATE('de', '/film/{0}/poster/{1}')),
('/movie/{0}/poster/{1}/edit',   COLUMN_CREATE('de', '/film/{0}/poster/{1}/bearbeiten')),
('/movie/{0}/poster/{1}/delete', COLUMN_CREATE('de', '/film/{0}/poster/{1}/löschen')),

-- Movie Lobby Card(s)
('/movie/{0}/lobby-cards',           COLUMN_CREATE('de', '/film/{0}/aushangbilder')),
('/movie/{0}/lobby-card/upload',     COLUMN_CREATE('de', '/film/{0}/aushangbild/hochladen')),
('/movie/{0}/lobby-card/{1}',        COLUMN_CREATE('de', '/film/{0}/aushangbild/{1}')),
('/movie/{0}/lobby-card/{1}/edit',   COLUMN_CREATE('de', '/film/{0}/aushangbild/{1}/bearbeiten')),
('/movie/{0}/lobby-card/{1}/delete', COLUMN_CREATE('de', '/film/{0}/aushangbild/{1}/löschen')),

-- Movie Photo(s)
('/movie/{0}/photos',           COLUMN_CREATE('de', '/film/{0}/fotos')),
('/movie/{0}/photo/upload',     COLUMN_CREATE('de', '/film/{0}/foto/hochladen')),
('/movie/{0}/photo/{1}',        COLUMN_CREATE('de', '/film/{0}/foto/{1}')),
('/movie/{0}/photo/{1}/edit',   COLUMN_CREATE('de', '/film/{0}/foto/{1}/bearbeiten')),
('/movie/{0}/photo/{1}/delete', COLUMN_CREATE('de', '/film/{0}/foto/{1}/löschen')),

-- Movie Release
('/movie/{0}/release/create',         COLUMN_CREATE('de', '/film/{0}/veröffentlichung/erstellen')),
('/movie/{0}/release/{1}',            COLUMN_CREATE('de', '/film/{0}/veröffentlichung/{1}')),
('/movie/{0}/release/{1}/discussion', COLUMN_CREATE('de', '/film/{0}/veröffentlichung/{1}/diskussion')),
('/movie/{0}/release/{1}/edit',       COLUMN_CREATE('de', '/film/{0}/veröffentlichung/{1}/bearbeiten')),
('/movie/{0}/release/{1}/delete',     COLUMN_CREATE('de', '/film/{0}/veröffentlichung/{1}/löschen')),

-- Persons
('/persons',               COLUMN_CREATE('de', '/personen')),
('/person',                COLUMN_CREATE('de', '/person')),
('/person/create',         COLUMN_CREATE('de', '/person/erstellen')),
('/person/{0}',            COLUMN_CREATE('de', '/person/{0}')),
('/person/{0}/discussion', COLUMN_CREATE('de', '/person/{0}/diskussion')),
('/person/{0}/edit',       COLUMN_CREATE('de', '/person/{0}/bearbeiten')),
('/person/{0}/delete',     COLUMN_CREATE('de', '/person/{0}/löschen')),

-- Person Photo(s)
('/person/{0}/photos',            COLUMN_CREATE('de', '/person/{0}/fotos')),
('/person/{0}/photo/upload',      COLUMN_CREATE('de', '/person/{0}/foto/hochladen')),
('/person/{0}/photo/{1}',         COLUMN_CREATE('de', '/person/{0}/foto/{1}')),
('/person/{0}/photo/{1}/edit',    COLUMN_CREATE('de', '/person/{0}/foto/{1}/bearbeiten')),
('/person/{0}/photo/{1}/delete',  COLUMN_CREATE('de', '/person/{0}/foto/{1}/löschen')),

-- Profile
('/profile',                       COLUMN_CREATE('de', '/profil')),
('/profile/login',                 COLUMN_CREATE('de', '/profil/anmeldung')),
('/profile/registration',          COLUMN_CREATE('de', '/profil/registrierung')),
('/profile/reset-password',        COLUMN_CREATE('de', '/profil/passwort-zurücksetzen')),
('/profile/sign-out',              COLUMN_CREATE('de', '/profil/abmelden')),
('/profile/account-settings',      COLUMN_CREATE('de', '/profil/kontoeinstellungen')),
('/profile/notification-settings', COLUMN_CREATE('de', '/profil/benachrichtigungseinstellungen')),
('/profile/email-settings',        COLUMN_CREATE('de', '/profil/e-mail-einstellungen')),
('/profile/password-settings',     COLUMN_CREATE('de', '/profil/passworteinstellungen')),
('/profile/danger-zone',           COLUMN_CREATE('de', '/profil/gefahrenzone')),

-- User(s)
('/users',               COLUMN_CREATE('de', '/benutzer')),
('/user',                COLUMN_CREATE('de', '/benutzer')),
('/user/{0}',            COLUMN_CREATE('de', '/benutzer/{0}')),
('/user/{0}/contact',    COLUMN_CREATE('de', '/benutzer/{0}/kontakt')),
('/user/{0}/collection', COLUMN_CREATE('de', '/benutzer/{0}/sammlung')),

-- Countries / Country
-- Countries are created with Intl ICU data and aren't editable.
('/countries',   COLUMN_CREATE('de', '/länder')),
('/country',     COLUMN_CREATE('de', '/land')),
('/country/{0}', COLUMN_CREATE('de', '/land/{0}')),

-- Year(s)
-- Years are generated dynamically and aren't editable.
('/years',    COLUMN_CREATE('de', '/jahre')),
('/year',     COLUMN_CREATE('de', '/jahr')),
('/year/{0}', COLUMN_CREATE('de', '/jahr/{0}')),

-- Genre(s)
('/genres',               COLUMN_CREATE('de', '/genres')),
('/genre',                COLUMN_CREATE('de', '/genre')),
('/genre/create',         COLUMN_CREATE('de', '/genre/erstellen')),
('/genre/{0}',            COLUMN_CREATE('de', '/genre/{0}')),
('/genre/{0}/discussion', COLUMN_CREATE('de', '/genre/{0}/diskussion')),
('/genre/{0}/edit',       COLUMN_CREATE('de', '/genre/{0}/bearbeiten')),
('/genre/{0}/löschen',    COLUMN_CREATE('de', '/genre/{0}/löschen')),

-- Style(s)
('/styles',               COLUMN_CREATE('de', '/stilrichtungen')),
('/style',                COLUMN_CREATE('de', '/stilrichtung')),
('/style/create',         COLUMN_CREATE('de', '/stilrichtung/erstellen')),
('/style/{0}',            COLUMN_CREATE('de', '/stilrichtung/{0}')),
('/style/{0}/discussion', COLUMN_CREATE('de', '/stilrichtung/{0}/diskussion')),
('/style/{0}/edit',       COLUMN_CREATE('de', '/stilrichtung/{0}/bearbeiten')),
('/style/{0}/löschen',    COLUMN_CREATE('de', '/stilrichtung/{0}/löschen'));
