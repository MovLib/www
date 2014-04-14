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
TRUNCATE TABLE `help_subcategories`;
TRUNCATE TABLE `help_articles`;

-- START "Help Categories"

INSERT INTO `help_categories` SET
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
SET @help_category_database = LAST_INSERT_ID();

INSERT INTO `help_categories` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Marketplace',
    'de', 'Marktplatz'
  ),
  `dyn_descriptions` = COLUMN_CREATE(
    'en', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.',
    'de', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.'
  ),
  `icon`             = 'ico-marketplace'
;
SET @help_category_marketplace = LAST_INSERT_ID();

INSERT INTO `help_categories` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Community',
    'de', 'Community'
  ),
  `dyn_descriptions` = COLUMN_CREATE(
    'en', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.',
    'de', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.'
  ),
  `icon`             = 'ico-person'
;
SET @help_category_community = LAST_INSERT_ID();

-- END "Help Categories"



-- START "Help Subcategories"

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Movies',
    'de', 'Filme'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-movie'
;
SET @help_subcategory_movies = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Series',
    'de', 'Serien'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-series'
;
SET @help_subcategory_series = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Releases',
    'de', 'Veröffentlichungen'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-release'
;
SET @help_subcategory_releases = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Persons',
    'de', 'Personen'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-person'
;
SET @help_subcategory_persons = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Companies',
    'de', 'Unternehmen'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-company'
;
SET @help_subcategory_companies = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Awards',
    'de', 'Auszeichnungen'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-award'
;
SET @help_subcategory_awards = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Events',
    'de', 'Events'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-event'
;
SET @help_subcategory_events = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Genres',
    'de', 'Genres'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-genre'
;
SET @help_subcategory_genres = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Jobs',
    'de', 'Tätigkeiten'
  ),
  `help_category_id` = @help_category_database,
  `icon`             = 'ico-job'
;
SET @help_subcategory_jobs = LAST_INSERT_ID();

INSERT INTO `help_subcategories` SET
  `dyn_titles`       = COLUMN_CREATE(
    'en', 'Deletion Requests',
    'de', 'Löschanträge'
  ),
  `help_category_id` = @help_category_community,
  `icon`             = 'ico-delete'
;
SET @help_subcategory_deletion_requests = LAST_INSERT_ID();

-- END "Help Subcategories"



-- START "Help Articles"

INSERT INTO `help_articles` SET
  `help_category_id`    = @help_category_database,
  `help_subcategory_id` = @help_subcategory_movies,
  `dyn_titles`          = COLUMN_CREATE(
    'en', 'Ratings',
    'de', 'Bewertungen'
  ),
  `dyn_texts`           = COLUMN_CREATE(
    'en', 'Ratings help article.',
    'de', 'Bewertungshilfeartikel.'
  )
;

INSERT INTO `help_articles` SET
  `help_category_id`    = @help_category_database,
  `help_subcategory_id` = @help_subcategory_movies,
  `dyn_titles`          = COLUMN_CREATE(
    'en', 'Create New Movie',
    'de', 'Neuen Film Anlegen'
  ),
  `dyn_texts`           = COLUMN_CREATE(
    'en', 'Help article to create a new movie.',
    'de', 'Hilfe Artikel um einen neuen Film anzulegen.'
  )
;

INSERT INTO `help_articles` SET
  `help_category_id`    = @help_category_database,
  `dyn_titles`          = COLUMN_CREATE(
    'en', 'Licenses',
    'de', 'Lizenzen'
  ),
  `dyn_texts`           = COLUMN_CREATE(
    'en', 'Help article on licenses used at MovLib.',
    'de', 'Hilfe Artikel über Lizenzen unter denendie filmbezogenen Daten auf MovLib stehen.'
  )
;

-- End "Help Articles"