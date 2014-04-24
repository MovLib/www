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

TRUNCATE TABLE `movies_countries`;
TRUNCATE TABLE `movies_trailers`;
TRUNCATE TABLE `movies`;
TRUNCATE TABLE `persons`;

-- START "Roundhay Garden Scene"

INSERT INTO `movies` SET
  `created`       = '2013-11-28 15:13:42',
  `year`          = 1888,
  `runtime`       = 60, -- 1 minute
  `dyn_synopses`  = COLUMN_CREATE(
    'en', '&lt;p&gt;The scene features Adolphe Le Prince, Sarah Whitley, Joseph Whitley and Harriet Whitley in the Roundhay Garden.&lt;/p&gt;',
    'de', '&lt;p&gt;Die Szene zeigt Adolphe Le Prince, Sarah Whitley, Joseph Whitley und Harriet Whitley im Roundhay Garden.&lt;/p&gt;'
  ),
  `dyn_wikipedia` = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/Roundhay_Garden_Scene',
    'de', 'https://de.wikipedia.org/wiki/Roundhay_Garden_Scene'
  )
;
SET @roundhay_garden_scene_id = LAST_INSERT_ID();

INSERT INTO `movies_titles` SET
  `movie_id`      = @roundhay_garden_scene_id,
  `dyn_comments`  = '',
  `title`         = 'Roundhay Garden Scene',
  `language_code` = 'en'
;
SET @roundhay_garden_scene_ot = LAST_INSERT_ID();

INSERT INTO `movies_original_titles` SET
  `movie_id` = @roundhay_garden_scene_id,
  `title_id` = @roundhay_garden_scene_ot
;

INSERT INTO `movies_countries` SET `movie_id` = @roundhay_garden_scene_id, `country_code` = 'GB';
INSERT INTO `movies_languages` SET `movie_id` = @roundhay_garden_scene_id, `language_code` = 'xx';
INSERT INTO `movies_genres` SET `movie_id` = @roundhay_garden_scene_id, `genre_id` = (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Short Film' LIMIT 1);

INSERT INTO `persons` SET
  `name`                   = 'Louis Le Prince',
  `born_name`              = 'Louis Aimé Augustin Le Prince',
  `birthdate`              = '1841-08-28',
  `deathdate`              = '1890-09-16',
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `image_cache_buster`     = UNHEX('27797ff26adb47b2c91630793ea342f4'),
  `image_width`            = 363,
  `image_height`           = 363,
  `image_filesize`         = 42010,
  `image_extension`        = 'jpg',
  `dyn_image_descriptions` = COLUMN_CREATE(
    'en', '&lt;p&gt;French cinema pioneer “Louis Le Prince”, the photo was taken from an unknown photographer in the 1880s.&lt;/p&gt;&lt;p&gt;The photo is public domain, see image source for exact licensing information: &lt;a href="https://commons.wikimedia.org/wiki/File%3ALouis_Le_Prince.jpg" rel="nofollow" target=_blank"&gt;Wikimedia Commons&lt;/a&gt;&lt;/p&gt;',
    'de', '&lt;p&gt;Der französische Kino-Pionier „Louis Le Prince”, das Foto wurde von einem unbekannten Fotografen in den 1880er Jahren erstellt.&lt;/p&gt;&lt;p&gt;Das Foto ist gemeinfrei, genaue Lizenzinformationen können der Quelle entnommen werden: &lt;a href="https://commons.wikimedia.org/wiki/File%3ALouis_Le_Prince.jpg" rel="nofollow" target=_blank"&gt;Wikimedia Commons&lt;/a&gt;&lt;/p&gt;'
  ),
  `image_styles`           = 'a:2:{s:2:"s1";O:28:"MovLib\\Data\\Image\\ImageStyle":3:{s:6:"height";i:60;s:6:"effect";O:29:"MovLib\\Data\\Image\\ImageEffect":5:{s:4:"crop";b:0;s:6:"filter";s:7:"Lanczos";s:6:"height";N;s:7:"quality";i:80;s:5:"width";i:60;}s:5:"width";i:60;}s:2:"s2";O:28:"MovLib\\Data\\Image\\ImageStyle":3:{s:6:"height";i:140;s:6:"effect";O:29:"MovLib\\Data\\Image\\ImageEffect":5:{s:4:"crop";b:0;s:6:"filter";s:7:"Lanczos";s:6:"height";N;s:7:"quality";i:80;s:5:"width";i:140;}s:5:"width";i:140;}}',
  `image_uploader_id`          = 1
;

SET @louis_le_prince_id = LAST_INSERT_ID();

INSERT INTO `movies_crew` SET `movie_id` = @roundhay_garden_scene_id, `person_id` = @louis_le_prince_id, `job_id` = 2, `dyn_role` = '';

INSERT INTO `persons` SET
  `name`                   = 'Harriet Hartley',
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `dyn_image_descriptions` = ''
;
SET @harriet_hartley_id = LAST_INSERT_ID();
INSERT INTO `movies_crew` SET `movie_id` = @roundhay_garden_scene_id, `person_id` = @harriet_hartley_id, `job_id` = 1, `dyn_role` = '';

INSERT INTO `persons` SET
  `name`                   = 'Adolphe Le Prince',
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `dyn_image_descriptions` = ''
;
SET @adolphe_le_prince_id = LAST_INSERT_ID();
INSERT INTO `movies_crew` SET `movie_id` = @roundhay_garden_scene_id, `person_id` = @adolphe_le_prince_id, `job_id` = 1, `dyn_role` = '', `role_id` = @adolphe_le_prince_id;

INSERT INTO `persons` SET
  `name`                   = 'Joseph Whitley',
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `dyn_image_descriptions` = ''
;
SET @joseph_whitley_id = LAST_INSERT_ID();
INSERT INTO `movies_crew` SET `movie_id` = @roundhay_garden_scene_id, `person_id` = @joseph_whitley_id, `job_id` = 1, `dyn_role` = '', `role_id` = @joseph_whitley_id;

INSERT INTO `persons` SET
  `name`                   = 'Sarah Whitley',
  `born_name`              = 'Sarah Robinson',
  /*`birthdate`       = '1816-00-00',*/
  `deathdate`              = '1888-10-24',
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `dyn_image_descriptions` = ''
;
SET @sarah_whitley_id = LAST_INSERT_ID();
INSERT INTO `movies_crew` SET `movie_id` = @roundhay_garden_scene_id, `person_id` = @sarah_whitley_id, `job_id` = 1, `dyn_role` = '', `role_id` = @sarah_whitley_id;

-- END "Roundhay Garden Scene"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "Big Buck Bunny"

INSERT INTO `movies` SET
  `created`       = '2013-11-29 14:01:56',
  `year`          = 2008,
  `runtime`       = 600, -- 10 minutes
  `dyn_synopses`  = COLUMN_CREATE(
    'en', '&lt;p&gt;“Big” Buck is a chubby bunny who enjoys the beauty of nature. But he decides to shed his gentleness when the flying squirrel Frank, the squirrel Rinky and the chinchilla Gamera kill two butterflies and throw fruits and nuts at him. Buck prepares a well-deserved revenge for the three rodents.&lt;/p&gt;',
    'de', '&lt;p&gt;„Big” Buck is ein fülliges Kaninchen, dass sich an der schönen Natur erfreut. Als jedoch das Flughörnchen Frank, das Eichhörnchen Rinky und das Chinchilla Gamera auftauchen, zwei Schmetterlinge töten und das Kaninchen mit Früchten und Nüssen bewerfen, beschließt es, seine Sanftmütigkeit abzulegen und an den Nagetieren Rache zu nehmen.&lt;/p&gt;'
  ),
  `dyn_wikipedia` = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/Big_Buck_Bunny',
    'de', 'https://de.wikipedia.org/wiki/Big_Buck_Bunny'
  )
;
SET @big_buck_bunny_id = LAST_INSERT_ID();

INSERT INTO `movies_titles` SET
  `movie_id`      = @big_buck_bunny_id,
  `dyn_comments`  = '',
  `language_code` = 'en',
  `title`         = 'Big Buck Bunny'
;
SET @big_buck_bunny_ot = LAST_INSERT_ID();

INSERT INTO `movies_original_titles` SET
  `movie_id` = @big_buck_bunny_id,
  `title_id` = @big_buck_bunny_ot
;

INSERT INTO `movies_taglines` SET
  `dyn_comments`  = '',
  `movie_id`      = @big_buck_bunny_id,
  `language_code` = 'en',
  `tagline`       = 'Don’t mess with the bunny'
;
SET @big_buck_bunny_tagline = LAST_INSERT_ID();

INSERT INTO `movies_display_taglines` (`movie_id`, `tagline_id`, `language_code`) VALUES
(@big_buck_bunny_id, @big_buck_bunny_tagline, 'en')
,(@big_buck_bunny_id, @big_buck_bunny_tagline, 'de')
;

INSERT INTO `persons` SET
  `name`                   = 'Sacha Goedegebure',
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `image_cache_buster`     = UNHEX('99c33c66748c51322369d4a61041b34e'),
  `image_width`            = 363,
  `image_height`           = 363,
  `image_filesize`         = 42010,
  `image_extension`        = 'jpg',
  `dyn_image_descriptions` = '',
  `image_styles`           = 'a:2:{s:2:"s1";O:28:"MovLib\\Data\\Image\\ImageStyle":3:{s:6:"height";i:60;s:6:"effect";O:29:"MovLib\\Data\\Image\\ImageEffect":5:{s:4:"crop";b:0;s:6:"filter";s:7:"Lanczos";s:6:"height";N;s:7:"quality";i:80;s:5:"width";i:60;}s:5:"width";i:60;}s:2:"s2";O:28:"MovLib\\Data\\Image\\ImageStyle":3:{s:6:"height";i:140;s:6:"effect";O:29:"MovLib\\Data\\Image\\ImageEffect":5:{s:4:"crop";b:0;s:6:"filter";s:7:"Lanczos";s:6:"height";N;s:7:"quality";i:80;s:5:"width";i:140;}s:5:"width";i:140;}}',
  `image_uploader_id`          = 1
;

SET @sacha_goedegebure_id = LAST_INSERT_ID();

INSERT INTO `movies_crew` SET `movie_id` = @big_buck_bunny_id, `person_id` = @sacha_goedegebure_id, `job_id` = 2, `dyn_role` = '';

INSERT INTO `movies_countries` SET `movie_id` = @big_buck_bunny_id, `country_code` = 'US';
INSERT INTO `movies_languages` SET `movie_id` = @big_buck_bunny_id, `language_code` = 'xx';
INSERT INTO `movies_genres` SET `movie_id` = @big_buck_bunny_id, `genre_id` = (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Short Film' LIMIT 1);
INSERT INTO `movies_genres` SET `movie_id` = @big_buck_bunny_id, `genre_id` = (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Animation' LIMIT 1);

-- END "Big Buck Bunny"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "The Shawshank Redemption"

INSERT INTO `movies` SET
  `runtime`       = 8520, -- 142 minutes
  `year`          = 1994,
  `dyn_synopses`  = '',
  `dyn_wikipedia` = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/The_Shawshank_Redemption',
    'de', 'https://de.wikipedia.org/wiki/Die_Verurteilten'
  )
;
SET @the_shawshank_redemption_id = LAST_INSERT_ID();

INSERT INTO `movies_titles` SET
  `movie_id`      = @the_shawshank_redemption_id,
  `dyn_comments`  = '',
  `language_code` = 'en',
  `title`         = 'The Shawshank Redemption'
;
SET @the_shawshank_redemption_ot = LAST_INSERT_ID();

INSERT INTO `movies_original_titles` SET
  `movie_id` = @the_shawshank_redemption_id,
  `title_id` = @the_shawshank_redemption_ot
;

INSERT INTO `movies_taglines` SET
  `dyn_comments`  = '',
  `movie_id`      = @the_shawshank_redemption_id,
  `language_code` = 'en',
  `tagline`       = 'Fear can hold you prisoner. Hope can set you free.'
;
SET @the_shawshank_redemption_en_tagline = LAST_INSERT_ID();

INSERT INTO `movies_display_taglines` SET
  `movie_id`      = @the_shawshank_redemption_id,
  `tagline_id`    = @the_shawshank_redemption_en_tagline,
  `language_code` = 'en'
;

INSERT INTO `movies_taglines` SET
  `dyn_comments`  = '',
  `movie_id`      = @the_shawshank_redemption_id,
  `language_code` = 'de',
  `tagline`       = 'Entscheide Dich, ob Du leben oder sterben willst … nur darum geht es.'
;
SET @the_shawshank_redemption_de_tagline = LAST_INSERT_ID();

INSERT INTO `movies_display_taglines` SET
  `movie_id`      = @the_shawshank_redemption_id,
  `tagline_id`    = @the_shawshank_redemption_de_tagline,
  `language_code` = 'de'
;

INSERT INTO `persons` SET
  `name`                   = 'Frank Darabont',
  `birthdate`              = '1959-01-28',
  `birthplace_id`          = 97967307,
  `sex`                    = 1,
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `dyn_image_descriptions` = ''
;
SET @frank_darabont_id = LAST_INSERT_ID();

INSERT INTO `movies_crew` SET
  `movie_id`  = @the_shawshank_redemption_id,
  `person_id` = @frank_darabont_id,
  `job_id` = 2,
  `dyn_role` = ''
;

INSERT INTO `persons` SET
  `name`                   = 'Morgan Freeman',
  `birthdate`              = '1937-06-01',
  `sex`                    = 1,
  `dyn_biographies`        = '',
  `dyn_wikipedia`          = '',
  `dyn_image_descriptions` = ''
;
SET @morgan_freeman_id = LAST_INSERT_ID();

INSERT INTO `movies_crew` SET
  `movie_id`  = @the_shawshank_redemption_id,
  `person_id` = @morgan_freeman_id,
  `job_id` = 1,
  `dyn_role` = ''
;

INSERT INTO `movies_titles` SET
  `movie_id`      = @the_shawshank_redemption_id,
  `dyn_comments`  = COLUMN_CREATE(
    'en', 'Official title in German speaking countries.',
    'de', 'Offizieller Titel im deutschsprachigen Raum.'
  ),
  `language_code` = 'de',
  `title`         = 'Die Verurteilten'
;
SET @die_verurteilten_id = LAST_INSERT_ID();

INSERT INTO `movies_display_titles` SET
  `language_code` = 'de',
  `movie_id`      = @the_shawshank_redemption_id,
  `title_id`      = @die_verurteilten_id
;

INSERT INTO `movies_genres` SET `movie_id` = @the_shawshank_redemption_id, `genre_id` = (
  SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Crime' LIMIT 1
);

INSERT INTO `movies_genres` SET `movie_id` = @the_shawshank_redemption_id, `genre_id` = (
  SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Drama' LIMIT 1
);

-- END "The Shawshank Redemption"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "Ichi the Killer"

INSERT INTO `movies` SET
  `runtime`       = 7740, -- 192 minutes
  `year`          = 2001,
  `dyn_synopses`  = '',
  `dyn_wikipedia` = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/Ichi_the_Killer_%28film%29',
    'de', 'https://de.wikipedia.org/wiki/Ichi_the_Killer'
  )
;
SET @ichi_the_killer_id = LAST_INSERT_ID();

INSERT INTO `movies_titles` SET
  `movie_id`      = @ichi_the_killer_id,
  `dyn_comments`  = '',
  `language_code` = 'ja',
  `title`         = '殺し屋1'
;
SET @ichi_the_killer_ot = LAST_INSERT_ID();

INSERT INTO `movies_original_titles` SET
  `movie_id` = @ichi_the_killer_id,
  `title_id` = @ichi_the_killer_ot
;

INSERT INTO `movies_taglines` SET
  `dyn_comments`  = COLUMN_CREATE(
    'en', 'Love is pretty painful.',
    'de', 'Liebe ist ziemlich schmerzhaft.'
  ),
  `movie_id`      = @ichi_the_killer_id,
  `language_code` = 'ja',
  `tagline`       = '愛は、かなりイタイ。'
;
SET @ichi_the_killer_tagline = LAST_INSERT_ID();

INSERT INTO `movies_display_taglines` SET
  `movie_id`      = @ichi_the_killer_id,
  `tagline_id`    = @ichi_the_killer_tagline,
  `language_code` = 'en'
;

INSERT INTO `movies_display_taglines` SET
  `movie_id`      = @ichi_the_killer_id,
  `tagline_id`    = @ichi_the_killer_tagline,
  `language_code` = 'de'
;

INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES
(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Action' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Art House' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Comedy' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Crime' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Drama' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Fantasy' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Horror' LIMIT 1))
,(@ichi_the_killer_id, (SELECT `id` FROM `genres` WHERE COLUMN_GET(`dyn_names`, 'en' AS CHAR) = 'Splatter' LIMIT 1))
;

-- END "Ichi the Killer"
