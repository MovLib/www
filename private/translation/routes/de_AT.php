<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

/**
 * German route translations for singular forms.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
// @codeCoverageIgnoreStart
return [

  // ------------------------------------------------------------------------------------------------------------------- Movie
  "/movie"                => "/film",
  "/movie/create"         => "/film/erstellen",
  "/movie/{0}"            => "/film/{0}",
  "/movie/{0}/discussion" => "/film/{0}/diskussion",
  "/movie/{0}/edit"       => "/film/{0}/bearbeiten",
  "/movie/{0}/delete"     => "/film/{0}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Movie Poster
  "/movie/{0}/poster/upload"     => "/film/{0}/poster/hochladen",
  "/movie/{0}/poster/{1}"        => "/film/{0}/poster/{1}",
  "/movie/{0}/poster/{1}/edit"   => "/film/{0}/poster/{1}/bearbeiten",
  "/movie/{0}/poster/{1}/delete" => "/film/{0}/poster/{1}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Movie Lobby Card
  "/movie/{0}/lobby-card/upload"     => "/film/{0}/aushangbild/hochladen",
  "/movie/{0}/lobby-card/{1}"        => "/film/{0}/aushangbild/{1}",
  "/movie/{0}/lobby-card/{1}/edit"   => "/film/{0}/aushangbild/{1}/bearbeiten",
  "/movie/{0}/lobby-card/{1}/delete" => "/film/{0}/aushangbild/{1}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Movie Photo
  "/movie/{0}/photo/upload"     => "/film/{0}/foto/hochladen",
  "/movie/{0}/photo/{1}"        => "/film/{0}/foto/{1}",
  "/movie/{0}/photo/{1}/edit"   => "/film/{0}/foto/{1}/bearbeiten",
  "/movie/{0}/photo/{1}/delete" => "/film/{0}/foto/{1}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Movie Release
  "/movie/{0}/release/create"         => "/film/{0}/veröffentlichung/erstellen",
  "/movie/{0}/release/{1}"            => "/film/{0}/veröffentlichung/{1}",
  "/movie/{0}/release/{1}/discussion" => "/film/{0}/veröffentlichung/{1}/diskussion",
  "/movie/{0}/release/{1}/edit"       => "/film/{0}/veröffentlichung/{1}/bearbeiten",
  "/movie/{0}/release/{1}/delete"     => "/film/{0}/veröffentlichung/{1}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Person
  "/person"                => "/person",
  "/person/create"         => "/person/erstellen",
  "/person/{0}"            => "/person/{0}",
  "/person/{0}/discussion" => "/person/{0}/diskussion",
  "/person/{0}/edit"       => "/person/{0}/bearbeiten",
  "/person/{0}/delete"     => "/person/{0}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Person Photo
  "/person/{0}/photo/upload"     => "/person/{0}/foto/hochladen",
  "/person/{0}/photo/{1}"        => "/person/{0}/foto/{1}",
  "/person/{0}/photo/{1}/edit"   => "/person/{0}/foto/{1}/bearbeiten",
  "/person/{0}/photo/{1}/delete" => "/person/{0}/foto/{1}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Profile
  "/profile"                       => "/profil",
  "/profile/sign-in"               => "/profil/anmelden",
  "/profile/join"                  => "/profil/beitreten",
  "/profile/reset-password"        => "/profil/passwort-zurücksetzen",
  "/profile/sign-out"              => "/profil/abmelden",
  "/profile/account-settings"      => "/profil/kontoeinstellungen",
  "/profile/notification-settings" => "/profil/benachrichtigungseinstellungen",
  "/profile/email-settings"        => "/profil/e-mail-einstellungen",
  "/profile/password-settings"     => "/profil/passworteinstellungen",
  "/profile/danger-zone"           => "/profil/gefahrenzone",

  // ------------------------------------------------------------------------------------------------------------------- User
  "/user"                => "/benutzer",
  "/user/{0}"            => "/benutzer/{0}",
  "/user/{0}/contact"    => "/benutzer/{0}/kontakt",
  "/user/{0}/collection" => "/benutzer/{0}/sammlung",

  // ------------------------------------------------------------------------------------------------------------------- Help
  "/help"                => "/hilfe",
  "/help/create"         => "/hilfe/erstellen",
  "/help/{0}"            => "/hilfe/{0}",
  "/help/{0}/create"     => "/hilfe/{0}/erstellen",
  "/help/{0}/edit"       => "/hilfe/{0}/bearbeiten",
  "/help/{0}/delete"     => "/hilfe/{0}/löschen",
  "/help/{0}/{0}"        => "/hilfe/{0}/{0}",
  "/help/{0}/{0}/edit"   => "/hilfe/{0}/{0}/bearbeiten",
  "/help/{0}/{0}/delete" => "/hilfe/{0}/{0}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Country
  // Countries are created with Intl ICU data and aren"t editable.
  "/country"     => "/land",
  "/country/{0}" => "/land/{0}",

  // ------------------------------------------------------------------------------------------------------------------- Year
  // Years are generated dynamically and aren"t editable.
  "/year"     => "/jahr",
  "/year/{0}" => "/jahr/{0}",

  // ------------------------------------------------------------------------------------------------------------------- Genre
  "/genre"                => "/genre",
  "/genre/create"         => "/genre/erstellen",
  "/genre/{0}"            => "/genre/{0}",
  "/genre/{0}/discussion" => "/genre/{0}/diskussion",
  "/genre/{0}/edit"       => "/genre/{0}/bearbeiten",
  "/genre/{0}/löschen"    => "/genre/{0}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Style
  "/style"                => "/stilrichtung",
  "/style/create"         => "/stilrichtung/erstellen",
  "/style/{0}"            => "/stilrichtung/{0}",
  "/style/{0}/discussion" => "/stilrichtung/{0}/diskussion",
  "/style/{0}/edit"       => "/stilrichtung/{0}/bearbeiten",
  "/style/{0}/löschen"    => "/stilrichtung/{0}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- System Pages
  "/{0}/edit"   => "/{0}/bearbeiten",
  "/{0}/delete" => "/{0}/löschen",

  // ------------------------------------------------------------------------------------------------------------------- Queries
  "page"  => "seite",
  "token" => "token",

];
// @codeCoverageIgnoreEnd
