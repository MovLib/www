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
-- Poster seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2014 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO `posters` SET
  `id`               = 1,
  `movie_id`         = 2, -- Big Buck Bunny
  `license_id`       = (SELECT `id` FROM `licenses` WHERE `abbreviation` = 'CC BY 3.0' LIMIT 1),
  `country_code`     = 'US',
  `language_code`    = 'en',
  `date`             = '2008-03-25',
  `deleted`          = false,
  `width`            = 1500,
  `height`           = 2107,
  `filesize`         = 493629,
  `extension`        = 'jpg',
  `changed`          = '2013-11-28 15:13:42',
  `created`          = '2013-11-28 15:13:42',
  `dyn_authors`      = COLUMN_CREATE('en', '&lt;p&gt;Blender Foundation | www.blender.org&lt;/p&gt;'),
  `dyn_descriptions` = COLUMN_CREATE(
    'de', '&lt;p&gt;Offizielles Poster.&lt;/p&gt;',
    'en', '&lt;p&gt;Official poster.&lt;/p&gt;'
  ),
  `dyn_sources`      = COLUMN_CREATE('en', '&lt;a href="http://download.blender.org/peach/presskit.zip" rel="nofollow" target="_blank"&gt;http://download.blender.org/peach/presskit.zip&lt;/a&gt;'),
  `styles`           = '',
  `user_id`          = 1
;
