<?php

/* !
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
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
// @codeCoverageIgnoreStart
return [
  // ------------------------------------------------------------------------------------------------------------------- Movie
  "/movie"                                     => "/film",
  "/movie/create"                              => "/film/erstellen",
  "/movie/random"                              => "/film/zufällig",
  "/movie/{0}"                                 => "/film/{0}",
  "/movie/{0}/cast"                            => "/film/{0}/besetzung",
  "/movie/{0}/crew"                            => "/film/{0}/crew",
  "/movie/{0}/delete"                          => "/film/{0}/löschen",
  "/movie/{0}/discussion"                      => "/film/{0}/diskussion",
  "/movie/{0}/edit"                            => "/film/{0}/bearbeiten",
  "/movie/{0}/history"                         => "/film/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Movie Backdrop
  "/movie/{0}/backdrop"                        => "/film/{0}/hintergrund",
  "/movie/{0}/backdrop/upload"                 => "/film/{0}/hintergrund/hochladen",
  "/movie/{0}/backdrop/{1}"                    => "/film/{0}/hintergrund/{1}",
  "/movie/{0}/backdrop/{1}/delete"             => "/film/{0}/hintergrund/{1}/löschen",
  "/movie/{0}/backdrop/{1}/edit"               => "/film/{0}/hintergrund/{1}/bearbeiten",
  "/movie/{0}/backdrop/{1}/history"            => "/film/{0}/hintergrund/{1}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Movie Poster
  "/movie/{0}/poster"                          => "/film/{0}/poster",
  "/movie/{0}/poster/upload"                   => "/film/{0}/poster/hochladen",
  "/movie/{0}/poster/{1}"                      => "/film/{0}/poster/{1}",
  "/movie/{0}/poster/{1}/delete"               => "/film/{0}/poster/{1}/löschen",
  "/movie/{0}/poster/{1}/edit"                 => "/film/{0}/poster/{1}/bearbeiten",
  "/movie/{0}/poster/{1}/history"              => "/film/{0}/poster/{1}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Movie Lobby Card
  "/movie/{0}/lobby-card"                      => "/film/{0}/aushangbild",
  "/movie/{0}/lobby-card/upload"               => "/film/{0}/aushangbild/hochladen",
  "/movie/{0}/lobby-card/{1}"                  => "/film/{0}/aushangbild/{1}",
  "/movie/{0}/lobby-card/{1}/delete"           => "/film/{0}/aushangbild/{1}/löschen",
  "/movie/{0}/lobby-card/{1}/edit"             => "/film/{0}/aushangbild/{1}/bearbeiten",
  "/movie/{0}/lobby-card/{1}/history"          => "/film/{0}/aushangbild/{1}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Releases
  "/release"                                   => "/veröffentlichung",
  "/release/random"                            => "/veröffentlichung/zufällig",
  "/release/{0}"                               => "/veröffentlichung/{0}",
  "/release/{0}/discussion"                    => "/veröffentlichung/{0}/diskussion",
  "/release/{0}/edit"                          => "/veröffentlichung/{0}/bearbeiten",
  "/release/{0}/history"                       => "/veröffentlichung/{0}/geschichte",
  "/release/{0}/delete"                        => "/veröffentlichung/{0}/löschen",
  // ------------------------------------------------------------------------------------------------------------------- Releases
  "/medium"                                    => "/medium",
  "/medium/random"                             => "/medium/zufällig",
  "/medium/{0}"                                => "/medium/{0}",
  "/medium/{0}/edit"                           => "/medium/{0}/bearbeiten",
  "/medium/{0}/history"                        => "/medium/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Series
  "/series"                                    => "/serie",
  "/series/create"                             => "/serie/erstellen",
  "/series/random"                             => "/serie/zufällig",
  "/series/{0}"                                => "/serie/{0}",
  "/series/{0}/cast"                           => "/serie/{0}/besetzung",
  "/series/{0}/crew"                           => "/serie/{0}/crew",
  "/series/{0}/delete"                         => "/serie/{0}/löschen",
  "/series/{0}/discussion"                     => "/serie/{0}/diskussion",
  "/series/{0}/edit"                           => "/serie/{0}/bearbeiten",
  "/series/{0}/episode/create"                 => "/serie/{0}/episode/erstellen",
  "/series/{0}/history"                        => "/serie/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Season
  // Seasons are generated automatically and can't be edited.
  "/series/{0}/season"                         => "/serie/{0}/staffel",
  "/series/{0}/season/{1}"                     => "/serie/{0}/staffel/{1}",
  // ------------------------------------------------------------------------------------------------------------------- Episode
  "/series/{0}/season/{1}/episode"             => "/serie/{0}/staffel/{1}/episode",
  "/series/{0}/season/{1}/episode/create"      => "/serie/{0}/staffel/{1}/episode/erstellen",
  "/series/{0}/season/{1}/episode/{2}"         => "/serie/{0}/staffel-{1}/episode/{2}",
  "/series/{0}/season/{1}/episode/{2}/cast"    => "/serie/{0}/staffel-{1}/episode/{2}/besetzung",
  "/series/{0}/season/{1}/episode/{2}/crew"    => "/serie/{0}/staffel-{1}/episode/{2}/crew",
  "/series/{0}/season/{1}/episode/{2}/delete"  => "/serie/{0}/staffel-{1}/episode/{2}/löschen",
  "/series/{0}/season/{1}/episode/{2}/edit"    => "/serie/{0}/staffel-{1}/episode/{2}/bearbeiten",
  "/series/{0}/season/{1}/episode/{2}/history" => "/serie/{0}/staffel-{1}/episode/{2}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Company
  "/company"                                   => "/unternehmen",
  "/company/create"                            => "/unternehmen/erstellen",
  "/company/random"                            => "/unternehmen/zufällig",
  "/company/{0}"                               => "/unternehmen/{0}",
  "/company/{0}/delete"                        => "/unternehmen/{0}/löschen",
  "/company/{0}/discussion"                    => "/unternehmen/{0}/diskussion",
  "/company/{0}/edit"                          => "/unternehmen/{0}/bearbeiten",
  "/company/{0}/history"                       => "/unternehmen/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Company Logo
  "/company/{0}/logo"                          => "/unternehmen/{0}/logo",
  "/company/{0}/logo/delete"                   => "/unternehmen/{0}/logo/löschen",
  // ------------------------------------------------------------------------------------------------------------------- Person
  "/person"                                    => "/person",
  "/person/create"                             => "/person/erstellen",
  "/person/random"                             => "/person/zufällig",
  "/person/{0}"                                => "/person/{0}",
  "/person/{0}/delete"                         => "/person/{0}/löschen",
  "/person/{0}/discussion"                     => "/person/{0}/diskussion",
  "/person/{0}/edit"                           => "/person/{0}/bearbeiten",
  "/person/{0}/history"                        => "/person/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Person Photo
  "/person/{0}/photo"                          => "/person/{0}/foto",
  "/person/{0}/photo/delete"                   => "/person/{0}/foto/löschen",
  // ------------------------------------------------------------------------------------------------------------------- Profile
  "/profile"                                   => "/profil",
  "/profile/account-settings"                  => "/profil/kontoeinstellungen",
  "/profile/collection"                        => "/profil/sammlung",
  "/profile/danger-zone"                       => "/profil/gefahrenzone",
  "/profile/email-settings"                    => "/profil/e-mail-einstellungen",
  "/profile/join"                              => "/profil/beitreten",
  "/profile/lists"                             => "/profil/listen",
  "/profile/messages"                          => "/profil/nachrichten",
  "/profile/notification-settings"             => "/profil/benachrichtigungseinstellungen",
  "/profile/password-settings"                 => "/profil/passworteinstellungen",
  "/profile/reset-password"                    => "/profil/passwort-zurücksetzen",
  "/profile/sign-in"                           => "/profil/anmelden",
  "/profile/sign-out"                          => "/profil/abmelden",
  "/profile/wantlist"                          => "/profil/wunschliste",
  "/profile/watchlist"                         => "/profil/beobachtungsliste",
  // ------------------------------------------------------------------------------------------------------------------- Search
  "/search"                                    => "/suche",
  // ------------------------------------------------------------------------------------------------------------------- User
  "/user"                                      => "/benutzer",
  "/user/random"                               => "/benutzer/zufällig",
  "/user/{0}"                                  => "/benutzer/{0}",
  "/user/{0}/collection"                       => "/benutzer/{0}/sammlung",
  "/user/{0}/contact"                          => "/benutzer/{0}/kontakt",
  "/user/{0}/uploads"                          => "/benutzer/{0}/hochgeladen",
  "/user/{0}/edits"                            => "/benutzer/{0}/bearbeitungen",
  // ------------------------------------------------------------------------------------------------------------------- Help
  "/help"                                      => "/hilfe",
  "/help/movies"                               => "/hilfe/filme",
  "/help/movies/ratings"                       => "/hilfe/filme/bewertungen",
  "/help/movies/ratings/edit"                  => "/hilfe/filme/bewertungen/bearbeiten",
  // ------------------------------------------------------------------------------------------------------------------- Country
  // Countries are created with Intl ICU data and aren"t editable.
  "/country"                                   => "/land",
  "/country/{0}"                               => "/land/{0}",
  // ------------------------------------------------------------------------------------------------------------------- Year
  // Years are generated dynamically and aren"t editable.
  "/year"                                      => "/jahr",
  "/year/{0}"                                  => "/jahr/{0}",
  // ------------------------------------------------------------------------------------------------------------------- Genre
  "/genre"                                     => "/genre",
  "/genre/create"                              => "/genre/erstellen",
  "/genre/random"                              => "/genre/zufällig",
  "/genre/{0}"                                 => "/genre/{0}",
  "/genre/{0}/delete"                          => "/genre/{0}/löschen",
  "/genre/{0}/discussion"                      => "/genre/{0}/diskussion",
  "/genre/{0}/edit"                            => "/genre/{0}/bearbeiten",
  "/genre/{0}/history"                         => "/genre/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Job
  "/job"                                       => "/tätigkeit",
  "/job/create"                                => "/tätigkeit/erstellen",
  "/job/random"                                => "/tätigkeit/zufällig",
  "/job/{0}"                                   => "/tätigkeit/{0}",
  "/job/{0}/delete"                            => "/tätigkeit/{0}/löschen",
  "/job/{0}/discussion"                        => "/tätigkeit/{0}/diskussion",
  "/job/{0}/edit"                              => "/tätigkeit/{0}/bearbeiten",
  "/job/{0}/history"                           => "/tätigkeit/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Award
  "/award"                                     => "/auszeichnung",
  "/award/create"                              => "/auszeichnung/erstellen",
  "/award/random"                              => "/auszeichnung/zufällig",
  "/award/{0}"                                 => "/auszeichnung/{0}",
  "/award/{0}/delete"                          => "/auszeichnung/{0}/löschen",
  "/award/{0}/discussion"                      => "/auszeichnung/{0}/diskussion",
  "/award/{0}/edit"                            => "/auszeichnung/{0}/bearbeiten",
  "/award/{0}/history"                         => "/auszeichnung/{0}/geschichte",
  "/award/{0}/icon"                            => "/auszeichnung/{0}/icon",
  "/award/{0}/icon/delete"                     => "/auszeichnung/{0}/icon/löschen",
  // ------------------------------------------------------------------------------------------------------------------- Award Category
  "/award/{0}/category/create"                 => "/auszeichnung/{0}/kategorie/erstellen",
  "/award/{0}/category/{1}"                    => "/auszeichnung/{0}/kategorie/{1}",
  "/award/{0}/category/{1}/delete"             => "/auszeichnung/{0}/kategorie/{1}/löschen",
  "/award/{0}/category/{1}/discussion"         => "/auszeichnung/{0}/kategorie/{1}/diskussion",
  "/award/{0}/category/{1}/edit"               => "/auszeichnung/{0}/kategorie/{1}/bearbeiten",
  "/award/{0}/category/{1}/history"            => "/auszeichnung/{0}/kategorie/{1}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- Event
  "/event"                                     => "/event",
  "/event/create"                              => "/event/erstellen",
  "/event/random"                              => "/event/zufällig",
  "/event/{0}"                                 => "/event/{0}",
  "/event/{0}/delete"                          => "/event/{0}/löschen",
  "/event/{0}/discussion"                      => "/event/{0}/diskussion",
  "/event/{0}/edit"                            => "/event/{0}/bearbeiten",
  "/event/{0}/history"                         => "/event/{0}/geschichte",
  // ------------------------------------------------------------------------------------------------------------------- System Pages
  "/about-movlib"                              => "/über-movlib",
  "/about-movlib/edit"                         => "/über-movlib/bearbeiten",
  "/articles-of-association"                   => "/vereinsstatuten",
  "/articles-of-association/edit"              => "/vereinsstatuten/bearbeiten",
  "/contact"                                   => "/kontakt",
  "/contact/edit"                              => "/kontakt/bearbeiten",
  "/impressum"                                 => "/impressum",
  "/impressum/edit"                            => "/impressum/bearbeiten",
  "/privacy-policy"                            => "/datenschutzerklärung",
  "/privacy-policy/edit"                       => "/datenschutzerklärung/bearbeiten",
  "/team"                                      => "/team",
  "/team/edit"                                 => "/team/bearbeiten",
  "/terms-of-use"                              => "/nutzungsbedingungen",
  "/terms-of-use/edit"                         => "/nutzungsbedingungen/bearbeiten",
  // ------------------------------------------------------------------------------------------------------------------- Queries
  "page"                                       => "seite",
  "reason"                                     => "grund",
  "redirect_to"                                => "umleiten_nach",
  "token"                                      => "token",
];
// @codeCoverageIgnoreEnd
