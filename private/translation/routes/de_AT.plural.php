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
 * German route translations for plural forms.
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

  // ------------------------------------------------------------------------------------------------------------------- Movies
  "/movies"                => "/filme",
  "/movie/{0}/backdrops"   => "/film/{0}/hintergründe",
  "/movie/{0}/posters"     => "/film/{0}/poster",
  "/movie/{0}/lobby-cards" => "/film/{0}/aushangbilder",
  "/movie/{0}/releases"    => "/film/{0}/veröffentlichungen",
  "/movie/{0}/titles"      => "/film/{0}/titel",

  // ------------------------------------------------------------------------------------------------------------------- Companies
  "/companies"         => "/unternehmen",


  // ------------------------------------------------------------------------------------------------------------------- Persons
  "/persons"           => "/personen",

  // ------------------------------------------------------------------------------------------------------------------- Users
  "/users" => "/benutzer",

  // ------------------------------------------------------------------------------------------------------------------- Help
  "/help" => "/hilfen",

  // ------------------------------------------------------------------------------------------------------------------- Countries
  // Countries are created with Intl ICU data and aren"t editable.
  "/countries"             => "/länder",
  "/country/{0}/movies"    => "/land/{0}/filme",
  "/country/{0}/series"    => "/land/{0}/serien",
  "/country/{0}/releases"  => "/land/{0}/veröffentlichungen",
  "/country/{0}/persons"   => "/land/{0}/personen",
  "/country/{0}/companies" => "/land/{0}/unternehmen",

  // ------------------------------------------------------------------------------------------------------------------- Years
  // Years are generated dynamically and aren"t editable.
  "/years"              => "/jahre",
  "/year/{0}/movies"    => "/jahr/{0}/filme",
  "/year/{0}/series"    => "/jahr/{0}/serien",
  "/year/{0}/releases"  => "/jahr/{0}/veröffentlichungen",
  "/year/{0}/persons"   => "/jahr/{0}/personen",
  "/year/{0}/companies" => "/jahr/{0}/unternehmen",

  // ------------------------------------------------------------------------------------------------------------------- Deletion
  "/deletion-requests" => "/löschanträge",

  // ------------------------------------------------------------------------------------------------------------------- Genres
  "/genres"           => "/genres",
  "/genre/{0}/movies" => "/genre/{0}/filme",
  "/genre/{0}/series" => "/genre/{0}/serien",

];
// @codeCoverageIgnoreEnd
