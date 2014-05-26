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
-- Revision seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `revisions`;

-- START "Revisions"

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `movies`.`changed`, 1, `movies`.`id`, 3 FROM `movies`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `series`.`changed`, 2, `series`.`id`, 3 FROM `series`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `releases`.`changed`, 3, `releases`.`id`, 3 FROM `releases`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `persons`.`changed`, 4, `persons`.`id`, 2 FROM `persons`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `companies`.`changed`, 5, `companies`.`id`, 3 FROM `companies`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `awards`.`changed`, 6, `awards`.`id`, 3 FROM `awards`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `awards_categories`.`changed`, 7, `awards_categories`.`id`, 3 FROM `awards_categories`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `events`.`changed`, 8, `events`.`id`, 3 FROM `events`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `genres`.`changed`, 9, `genres`.`id`, 1 FROM `genres`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `jobs`.`changed`, 10, `jobs`.`id`, 3 FROM `jobs`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `help_articles`.`changed`, 11, `help_articles`.`id`, 3 FROM `help_articles`;

-- Movie Poster
-- INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
--   SELECT `movies`.`changed`, 12, `movies`.`id`, 3 FROM `movies`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `system_pages`.`changed`, 13, `system_pages`.`id`, 3 FROM `system_pages`;

-- END "Revisions"
