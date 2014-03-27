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
 * German route translations for plural forms.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
// @codeCoverageIgnoreStart
return [
  // ------------------------------------------------------------------------------------------------------------------- Movies
  "/movies"                       => "/filme",
  "/movie/charts"                 => "/film/charts",
  "/movie/{0}/backdrops"          => "/film/{0}/hintergründe",
  "/movie/{0}/posters"            => "/film/{0}/poster",
  "/movie/{0}/lobby-cards"        => "/film/{0}/aushangbilder",
  "/movie/{0}/titles"             => "/film/{0}/titel",
  // ------------------------------------------------------------------------------------------------------------------- Series
  "/series"                       => "/serien",
  "/series/charts"                => "/serien/charts",
  "/series/{0}/seasons"           => "/serie/{0}/staffeln",
  // ------------------------------------------------------------------------------------------------------------------- Releases
  "/releases"                     => "/veröffentlichungen",
  // ------------------------------------------------------------------------------------------------------------------- Media
  "/media"                        => "/medien",
  // ------------------------------------------------------------------------------------------------------------------- Companies
  "/companies"                    => "/unternehmen",
  "/company/{0}/movies"           => "/unternehmen/{0}/filme",
  "/company/{0}/series"           => "/unternehmen/{0}/serien",
  "/company/{0}/releases"         => "/unternehmen/{0}/veröffentlichungen",
  // ------------------------------------------------------------------------------------------------------------------- Persons
  "/persons"                      => "/personen",
  "/person/{0}/movies"            => "/person/{0}/filme",
  "/person/{0}/series"            => "/person/{0}/serien",
  "/person/{0}/releases"          => "/person/{0}/veröffentlichungen",
  // ------------------------------------------------------------------------------------------------------------------- Users
  "/users"                        => "/benutzer",
  // ------------------------------------------------------------------------------------------------------------------- Countries
  // Countries are created with Intl ICU data and aren"t editable.
  "/countries"                    => "/länder",
  "/country/{0}/movies"           => "/land/{0}/filme",
  "/country/{0}/series"           => "/land/{0}/serien",
  "/country/{0}/releases"         => "/land/{0}/veröffentlichungen",
  "/country/{0}/persons"          => "/land/{0}/personen",
  "/country/{0}/companies"        => "/land/{0}/unternehmen",
  // ------------------------------------------------------------------------------------------------------------------- Years
  // Years are generated dynamically and aren"t editable.
  "/years"                        => "/jahre",
  "/year/{0}/movies"              => "/jahr/{0}/filme",
  "/year/{0}/series"              => "/jahr/{0}/serien",
  "/year/{0}/releases"            => "/jahr/{0}/veröffentlichungen",
  "/year/{0}/persons"             => "/jahr/{0}/personen",
  "/year/{0}/companies"           => "/jahr/{0}/unternehmen",
  // ------------------------------------------------------------------------------------------------------------------- Deletion
  "/deletion-requests"            => "/löschanträge",
  // ------------------------------------------------------------------------------------------------------------------- Genres
  "/genres"                       => "/genres",
  "/genre/{0}/movies"             => "/genre/{0}/filme",
  "/genre/{0}/series"             => "/genre/{0}/serien",
  // ------------------------------------------------------------------------------------------------------------------- Awards
  "/awards"                       => "/auszeichnungen",
  "/award/{0}/movies"             => "/auszeichnung/{0}/filme",
  "/award/{0}/series"             => "/auszeichnung/{0}/serien",
  // ------------------------------------------------------------------------------------------------------------------- Award Categories
  "/award/{0}/categories"          => "/auszeichnung/{0}/kategorien",
  "/award/{0}/category/{1}/movies" => "/auszeichnung/{0}/kategorie/{1}/filme",
  "/award/{0}/category/{1}/series" => "/auszeichnung/{0}/kategorie/{1}/serien",
  // ------------------------------------------------------------------------------------------------------------------- Award Events
  "/award/{0}/events"             => "/auszeichnung/{0}/events",
  // ------------------------------------------------------------------------------------------------------------------- Events
  "/events"                       => "/events",
  "/event/{0}/movies"             => "/event/{0}/filme",
  "/event/{0}/series"             => "/event/{0}/serien",
  // ------------------------------------------------------------------------------------------------------------------- Jobs
  "/jobs"                         => "/tätigkeiten",
  "/job/{0}/movies"               => "/tätigkeit/{0}/filme",
  "/job/{0}/series"               => "/tätigkeit/{0}/serien",
];
// @codeCoverageIgnoreEnd
