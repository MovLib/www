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
  "/movie/{0}/posters"     => "/film/{0}/poster",
  "/movie/{0}/lobby-cards" => "/film/{0}/aushangbilder",
  "/movie/{0}/photos"      => "/film/{0}/fotos",
  "/movie/{0}/releases"    => "/film/{0}/veröffentlichungen",
  "/movie/{0}/titles"      => "/film/{0}/titel",

  // ------------------------------------------------------------------------------------------------------------------- Persons
  "/persons"           => "/personen",
  "/person/{0}/photos" => "/person/{0}/fotos",

  // ------------------------------------------------------------------------------------------------------------------- Users
  "/users" => "/benutzer",

  // ------------------------------------------------------------------------------------------------------------------- Help
  "/help" => "/hilfen",

  // ------------------------------------------------------------------------------------------------------------------- Countries
  // Countries are created with Intl ICU data and aren"t editable.
  "/countries" => "/länder",

  // ------------------------------------------------------------------------------------------------------------------- Years
  // Years are generated dynamically and aren"t editable.
  "/years" => "/jahre",

  // ------------------------------------------------------------------------------------------------------------------- Genres
  "/genres" => "/genres",

];
// @codeCoverageIgnoreEnd
