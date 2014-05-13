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
-- Revision entity types seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `revision_entity_types`;

-- START "Revision Entity Types"

INSERT INTO `revision_entity_types` SET
  `id`            = 1,
  `name_property` = 'displayTitleAndYear',
  `type`          = 'Movie',
  `class`         = '\\MovLib\\Data\\Movie\\Movie'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 2,
  `name_property` = 'displayTitle',
  `type`          = 'Series',
  `class`         = '\\MovLib\\Data\\Series\\Series'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 3,
  `name_property` = 'title',
  `type`          = 'Release',
  `class`         = '\\MovLib\\Data\\Release\\Release'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 4,
  `name_property` = 'name',
  `type`          = 'Person',
  `class`         = '\\MovLib\\Data\\Person\\Person'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 5,
  `name_property` = 'name',
  `type`          = 'Company',
  `class`         = '\\MovLib\\Data\\Company\\Company'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 6,
  `name_property` = 'name',
  `type`          = 'Award',
  `class`         = '\\MovLib\\Data\\Award\\Award'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 7,
  `name_property` = 'name',
  `type`          = 'Award Category',
  `class`         = '\\MovLib\\Data\\Award\\Category'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 8,
  `name_property` = 'name',
  `type`          = 'Event',
  `class`         = '\\MovLib\\Data\\Event\\Event'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 9,
  `name_property` = 'name',
  `type`          = 'Genre',
  `class`         = '\\MovLib\\Data\\Genre\\Genre'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 10,
  `name_property` = 'names[0]',
  `type`          = 'Job',
  `class`         = '\\MovLib\\Data\\Job\\Job'
;

INSERT INTO `revision_entity_types` SET
  `id`            = 11,
  `name_property` = 'title',
  `type`          = 'Help Article',
  `class`         = '\\MovLib\\Data\\Help\\Article'
;

-- END "Revision Entity Types"
