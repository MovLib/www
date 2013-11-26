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
-- Movie seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `movies_directors`;
TRUNCATE TABLE `movies_images`;
TRUNCATE TABLE `movies`;
TRUNCATE TABLE `persons`;

-- START "Roundhay Garden Scene"

INSERT INTO `movies` SET
  `original_title` = 'Roundhay Garden Scene',
  `year`           = 1888,
  `runtime`        = 60, -- 1 minute
  `dyn_synopses`   = COLUMN_CREATE(
    'en', '&lt;p&gt;The scene features Adolphe Le Prince, Sarah Whitley, Joseph Whitley and Harriet Whitley in the Roundhay Garden.&lt;/p&gt;',
    'de', '&lt;p&gt;Die Szene zeigt Adolphe Le Prince, Sarah Whitley, Joseph Whitley und Harriet Whitley im Roundhay Garden.&lt;/p&gt;'
  )
;

INSERT INTO `persons` SET
  `name`            = 'Louis Le Prince',
  `born_name`       = 'Louis Aimé Augustin Le Prince',
  `birthdate`       = '1841-08-28',
  `deathdate`       = '1890-09-16',
  `dyn_aliases`     = '',
  `dyn_biographies` = '',
  `dyn_links`       = '',
  `country`         = 'FR'
;

INSERT INTO `movies_directors` SET `movie_id` = 1, `person_id` = 1;
INSERT INTO `movies_countries` SET `movie_id` = 1, `country_code` = 'UK';
INSERT INTO `movies_languages` SET `movie_id` = 1, `language_code` = 'xx';
INSERT INTO `movies_genres` SET `movie_id` = 1, `genre_id` = (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Short' LIMIT 1);

-- END "Roundhay Garden Scene"

-- START "Big Buck Bunny"

INSERT INTO `movies` SET
  `original_title` = 'Big Buck Bunny',
  `year`           = 2008,
  `runtime`        = 600, -- 10 minutes
  `dyn_synopses`   = COLUMN_CREATE(
    'en', '&lt;p&gt;“Big” Buck is a chubby bunny who enjoys the beauty of nature. But he decides to shed his gentleness when the flying squirrel Frank, the squirrel Rinky and the chinchilla Gamera kill two butterflies and throw fruits and nuts at him. Buck prepares a well-deserved revenge for the three rodents.&lt;/p&gt;',
    'de', '&lt;p&gt;„Big” Buck is ein fülliges Kaninchen, dass sich an der schönen Natur erfreut. Als jedoch das Flughörnchen Frank, das Eichhörnchen Rinky und das Chinchilla Gamera auftauchen, zwei Schmetterlinge töten und das Kaninchen mit Früchten und Nüssen bewerfen, beschließt es, seine Sanftmütigkeit abzulegen und an den Nagetieren Rache zu nehmen.&lt;/p&gt;'
  ),
  `website`        = 'http://www.bigbuckbunny.org/'
;

INSERT INTO `persons` SET
  `name`            = 'Sacha Goedegebure',
  `dyn_aliases`     = '',
  `dyn_biographies` = '',
  `dyn_links`       = '',
  `country`         = 'NL'
;

INSERT INTO `movies_directors` SET `movie_id`  = 2, `person_id` = 2;
INSERT INTO `movies_countries` SET `movie_id` = 2, `country_code` = 'US';
INSERT INTO `movies_languages` SET `movie_id` = 2, `language_code` = 'xx';
INSERT INTO `movies_genres` SET `movie_id` = 2, `genre_id` = (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Short' LIMIT 1);
INSERT INTO `movies_genres` SET `movie_id` = 2, `genre_id` = (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Animation' LIMIT 1);

INSERT INTO `movies_images` SET
  `id`               = 1,
  `movie_id`         = 2,
  `type_id`          = 1,
  `user_id`          = 1,
  `license_id`       = (SELECT `id` FROM `licenses` WHERE `abbreviation` = 'CC BY 3.0' LIMIT 1),
  `country_code`     = 'US',
  `width`            = 1500,
  `height`           = 2107,
  `size`             = 493629,
  `extension`        = 'jpg',
  `changed`          = CURRENT_TIMESTAMP,
  `created`          = CURRENT_TIMESTAMP,
  `dyn_descriptions` = '',
  `source`           = 'http://download.blender.org/peach/presskit.zip',
  `styles`           = 'a:4:{i:70;a:2:{s:5:"width";i:70;s:6:"height";i:99;}i:140;a:2:{s:5:"width";i:140;s:6:"height";i:197;}i:220;a:2:{s:5:"width";i:220;s:6:"height";i:309;}i:620;a:2:{s:5:"width";i:620;s:6:"height";i:871;}}'
;

-- END "Big Buck Bunny"
