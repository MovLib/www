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
-- Help articles seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `help_categories`;

-- START "Help Categories"

INSERT INTO `help_categories` SET
  `id`               = 1,
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Database',
    'de', 'Datenbank'
  ),
  `dyn_descriptions` = COLUMN_CREATE(
    'en', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.',
    'de', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.'
  ),
  `icon`             = 'ico-database'
;

INSERT INTO `help_categories` SET
  `id`               = 2,
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Marketplace',
    'de', 'Marktplatz'
  ),
  `dyn_descriptions` = COLUMN_CREATE(
    'en', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.',
    'de', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.'
  ),
  `icon`             = 'ico-marketplace'
;

INSERT INTO `help_categories` SET
  `id`               = 3,
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Community',
    'de', 'Community'
  ),
  `dyn_descriptions` = COLUMN_CREATE(
    'en', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.',
    'de', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.'
  ),
  `icon`             = 'ico-person'
;

-- END "Help Categories"
