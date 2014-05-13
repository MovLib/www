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

INSERT INTO `revisions` SET
  `entity_type_id`           = 1,
  `entity_id`                = 2,
  `created`                  = CURRENT_TIMESTAMP,
  `user_id`                  = 3,
  `commit_msg`               = 'Added german title.',
  `commit_msg_language_code` = 'en',
  `data`                     = 'a:0:{}'
;

INSERT INTO `revisions` SET
  `entity_type_id`           = 4,
  `entity_id`                = 6,
  `created`                  = CURRENT_TIMESTAMP,
  `user_id`                  = 3,
  `commit_msg`               = 'Added Wikipedia link.',
  `commit_msg_language_code` = 'en',
  `data`                     = 'a:0:{}'
;

INSERT INTO `revisions` SET
  `entity_type_id`           = 5,
  `entity_id`                = 3,
  `created`                  = CURRENT_TIMESTAMP,
  `user_id`                  = 3,
  `commit_msg`               = 'Changed description.',
  `commit_msg_language_code` = 'en',
  `data`                     = 'a:0:{}'
;

-- END "Revisions"


-- START "Image Revisions"

INSERT INTO `image_revisions` SET
  `entity_type_id`           = 12,
  `entity_id`                = 1,
  `created`                  = CURRENT_TIMESTAMP,
  `user_id`                  = 3,
  `commit_msg`               = 'Changed image.',
  `commit_msg_language_code` = 'en',
  `data`                     = 'a:0:{}',
  `path`                     = ''
;

INSERT INTO `image_revisions` SET
  `entity_type_id`           = 12,
  `entity_id`                = 2,
  `created`                  = CURRENT_TIMESTAMP,
  `user_id`                  = 3,
  `commit_msg`               = 'Changed image description.',
  `commit_msg_language_code` = 'en',
  `data`                     = 'a:0:{}'
;

-- END "Image Revisions"