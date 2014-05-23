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
  SELECT `genres`.`changed`, 9, `genres`.`id`, 1 FROM `genres`;

INSERT INTO `revisions` (`id`, `revision_entity_id`, `entity_id`, `user_id`)
  SELECT `jobs`.`changed`,10, `jobs`.`id`, 3 FROM `jobs`;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 17:19:31',
  `revision_entity_id` = 1,
  `entity_id`          = 1,
  `user_id`            = 3
;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 16:19:31',
  `revision_entity_id` = 2,
  `entity_id`          = 1,
  `user_id`            = 3
;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 14:19:31',
  `revision_entity_id` = 4,
  `entity_id`          = 1,
  `user_id`            = 3
;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 13:19:31',
  `revision_entity_id` = 5,
  `entity_id`          = 1,
  `user_id`            = 3
;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 12:19:31',
  `revision_entity_id` = 6,
  `entity_id`          = 1,
  `user_id`            = 3
;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 11:19:31',
  `revision_entity_id` = 7,
  `entity_id`          = 1,
  `user_id`            = 3
;

INSERT INTO `revisions` SET
  `id`                 = '2014-05-20 10:19:31',
  `revision_entity_id` = 8,
  `entity_id`          = 1,
  `user_id`            = 3
;

-- END "Revisions"
