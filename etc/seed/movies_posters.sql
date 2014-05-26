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
-- Seed the movies posters.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2014 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `posters`;
TRUNCATE TABLE `display_posters`;

INSERT INTO `posters` SET
  `created`          = CURRENT_TIMESTAMP,
  `changed`          = CURRENT_TIMESTAMP,
  `movie_id`         = 2,
  `uploader_id`      = 1,
  `cache_buster`     = UNHEX('6895875de2743ece69cdb0572fa81cab'),
  `deleted`          = false,
  `dyn_descriptions` = '',
  `extension`        = 'jpg',
  `filesize`         = 547472,
  `height`           = 2107,
  `language_code`    = 'en',
  `width`            = 1500,
  `country_code`     = 'US',
  `publishing_date`  = '2008-00-00'
;
SET @big_buck_bunny_movie_poster_id = LAST_INSERT_ID();

INSERT INTO `display_posters` (`movie_id`, `poster_id`, `language_code`) VALUES
  (2, @big_buck_bunny_movie_poster_id, 'en'),
  (2, @big_buck_bunny_movie_poster_id, 'de')
;
