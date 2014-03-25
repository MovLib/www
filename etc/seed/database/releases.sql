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
-- Release seed data.
--
-- @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2014 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `releases`;

-- START "Big Buck Bunny"

INSERT INTO `releases` SET
  `country_code`          = 'DE',
  `dyn_notes`             = '',
  `title`                 = 'Big Buck Bunny',
  `type`                  = 1,
  `publishing_date_sale`  = '2008-08-22',
  `bin_media_counts`      = 'a:1:{s:2:"BD";i:1;}'
;

SET @release_big_buck_bunny_id = LAST_INSERT_ID();

INSERT INTO `media` SET
  `format`    = 'Blu-ray',
  `dyn_notes` = ''
;

SET @medium_id = LAST_INSERT_ID();

INSERT INTO `releases_media` SET
  `release_id` = @release_big_buck_bunny_id,
  `medium_id`  = @medium_id
;

INSERT INTO `releases_labels` SET
  `release_id` = @release_big_buck_bunny_id,
  `company_id` = (SELECT `id` FROM `companies` WHERE `name` = 'Imagion AG' LIMIT 1)
;

INSERT INTO `media_movies` SET
  `medium_id` = @medium_id,
  `movie_id`  = (
    SELECT `movies`.`id` FROM `movies`
    INNER JOIN `movies_original_titles` ON `movies_original_titles`.`movie_id` = `movies`.`id`
    INNER JOIN `movies_titles` ON `movies_titles`.`id` = `movies_original_titles`.`title_id`
      AND `movies_titles`.`movie_id` = `movies`.`id`
    WHERE `movies_titles`.`title` = 'Big Buck Bunny'
    LIMIT 1
  ),
  `bin_medium_movie` = ''
;

-- END "Big Buck Bunny"
