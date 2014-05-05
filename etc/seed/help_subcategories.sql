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
-- Help subcategories seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `help_subcategories`;

-- START "Help Sub Categories"

INSERT INTO `help_subcategories` SET
  `id`               = 1,
  `title`            = 'Movies',
  `help_category_id` = 1,
  `icon`             = 'ico-movie'
;

INSERT INTO `help_subcategories` SET
  `id`               = 2,
  `title`            = 'Series',
  `help_category_id` = 1,
  `icon`             = 'ico-series'
;

INSERT INTO `help_subcategories` SET
  `id`               = 3,
  `title`            = 'Releases',
  `help_category_id` = 1,
  `icon`             = 'ico-release'
;

INSERT INTO `help_subcategories` SET
  `id`               = 4,
  `title`            = 'Persons',
  `help_category_id` = 1,
  `icon`             = 'ico-person'
;

INSERT INTO `help_subcategories` SET
  `id`               = 5,
  `title`            = 'Companies',
  `help_category_id` = 1,
  `icon`             = 'ico-company'
;

INSERT INTO `help_subcategories` SET
  `id`               = 6,
  `title`            = 'Awards',
  `help_category_id` = 1,
  `icon`             = 'ico-award'
;

INSERT INTO `help_subcategories` SET
  `id`               = 7,
  `title`            = 'Events',
  `help_category_id` = 1,
  `icon`             = 'ico-event'
;

INSERT INTO `help_subcategories` SET
  `id`               = 8,
  `title`            = 'Genres',
  `help_category_id` = 1,
  `icon`             = 'ico-genre'
;

INSERT INTO `help_subcategories` SET
  `id`               = 9,
  `title`            = 'Jobs',
  `help_category_id` = 1,
  `icon`             = 'ico-job'
;

INSERT INTO `help_subcategories` SET
  `id`               = 10,
  `title`            = 'Deletion Requests',
  `help_category_id` = 3,
  `icon`             = 'ico-delete'
;


-- END "Help Sub Categories"
