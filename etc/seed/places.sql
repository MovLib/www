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
-- Award seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

--
-- If you want to search for a place to create see data, simply use the following URL and add your place's name after
-- the 'q' parameter:
-- @link
--
-- If you want to start developing the JavaScript code with which our user's will be able to add place's to our database
-- use the following URL and add the search queries after the 'q' parameter:
-- @linkhttp://open.mapquestapi.com/nominatim/v1/search.php?format=json&osm_type=N&addressdetails=1&countrycodes=<ISO alpha-2 country codes>&q=<the query to search for>
--
-- It's essential that we're not going to create a live search and hit their servers too hard.
--

TRUNCATE TABLE `places`;

INSERT INTO `places` SET
  `id`           = 97967307,
  `country_code` = 'FR',
  `changed`      = CURRENT_TIMESTAMP,
  `created`      = CURRENT_TIMESTAMP,
  `dyn_names`    = '',
  `name`         = 'Montbéliard (Free County)',
  `latitude`     = 47.5102368,
  `longitude`    = 6.7977564
;

INSERT IGNORE INTO `places` SET
  `id`           = 97981472,
  `country_code` = 'US',
  `changed`      = CURRENT_TIMESTAMP,
  `created`      = CURRENT_TIMESTAMP,
  `dyn_names`    = '',
  `name`         = 'Novi (Michigan)',
  `latitude`     = -83.4754913,
  `longitude`    = 42.48059
;

INSERT INTO `places` SET
  `id`           = 2489342526,
  `country_code` = 'US',
  `changed`      = CURRENT_TIMESTAMP,
  `created`      = CURRENT_TIMESTAMP,
  `dyn_names`    = '',
  `name`         = 'Los Angeles (California)',
  `latitude`     = 89,
  `longitude`    = -118.24368
;
