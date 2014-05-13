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
  `id`    = 1,
  `class` = '\\MovLib\\Data\\Movie\\Movie'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 2,
  `class` = '\\MovLib\\Data\\Series\\Series'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 3,
  `class` = '\\MovLib\\Data\\Release\\Release'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 4,
  `class` = '\\MovLib\\Data\\Person\\Person'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 5,
  `class` = '\\MovLib\\Data\\Company\\Company'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 6,
  `class` = '\\MovLib\\Data\\Award\\Award'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 7,
  `class` = '\\MovLib\\Data\\Award\\Category'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 8,
  `class` = '\\MovLib\\Data\\Event\\Event'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 9,
  `class` = '\\MovLib\\Data\\Genre\\Genre'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 10,
  `class` = '\\MovLib\\Data\\Job\\Job'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 11,
  `class` = '\\MovLib\\Data\\Help\\Article'
;

INSERT INTO `revision_entity_types` SET
  `id`    = 12,
  `class` = '\\MovLib\\Data\\Movie\\Poster'
;

-- END "Revision Entity Types"
