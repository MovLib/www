<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Model;

use \MovLib\Exception\MovieException;
use \MovLib\Model\AbstractModel;

/**
 * The movie model is responsible for all database related functionality of a single movie entry.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieModel extends AbstractModel {

  // ------------------------------------------------------------------------------------------------------------------- Properties
  /**
   * The movie's id.
   * @var int
   */
  public $id;
  /**
   * The movie's original release title.
   *
   * @var string
   */
  public $originalTitle;
  /**
   * The movie's Bayesian rating.
   * @var float
   */
  public $rating;
  /**
   * The movie's statistical average rating.
   *
   * @var float
   */
  public $meanRating;
  /**
   * The movie's count of votes.
   *
   * @var int
   */
  public $votes;
  /**
   * The movie's deleted status.
   *
   * @var boolean
   */
  public $deleted;
  /**
   * The movie's release year.
   *
   * @var int
   */
  public $year;
  /**
   * The movie's approximate runtime in minutes.
   *
   * @var int
   */
  public $runtime;
  /**
   * The movie's global rank.
   *
   * @var int
   */
  public $rank;
  /**
   * The movie's localized synopsis.
   *
   * @var string
   */
  public $synopsis;
  /**
   * The movie's relationships to other movies (from the binary column).
   * @var array
   */
  private $relationships;

  // ------------------------------------------------------------------------------------------------------------------- Magic Methods

  /**
   * Construct new movie model from given ID and gather all movie information available in the movies table.
   * If no ID is supplied, an empty model will be returned.
   * If the ID is invalid a \MovLib\Exception\MovieException will be thrown.
   *
   * @param int $id
   *  The movie's id to construct this model from.
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @throws \MovLib\Exception\MovieException
   *  If the movie's ID is invalid.
   */
  public function __construct($id = null) {
    global $i18n;
    if (isset($id)) {
      $result = $this->select(
        "SELECT
          `movie_id` AS `id`,
          `original_title` AS `originalTitle`,
          `rating` AS `rating`,
          `mean_rating` AS `meanRating`,
          `votes`,
          `deleted`,
          `year`,
          `runtime`,
          `rank`,
          COLUMN_GET(`dyn_synopses`, '{$i18n->languageCode}' AS BINARY) AS `synopsis`,
          `bin_relationships` AS `relationships`
          FROM `movies`
          WHERE `movie_id` = ?
          LIMIT 1",
        "d",
        [ $id ]
      );
      if (isset($result[0]) === false) {
        throw new MovieException("Could not find movie with ID '{$id}'!");
      }
      foreach ($result[0] as $fieldName => $fieldValue) {
        $this->{$fieldName} = $fieldValue;
      }
      settype($this->deleted, "boolean");
      $this->relationships = igbinary_unserialize($this->relationships);
    }
  }

  /**
   * Retrieve the movie's awards from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $awards
   * @return array
   *  A keyed array containing the award information in an associative array.
   */
  public function getAwards() {
    global $i18n;
    static $awards = null;
    if ($awards === null) {
      $awards = $this->select(
        "SELECT
          a.`award_id` AS `id`,
          a.`name` AS `name`,
          COLUMN_GET(a.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `nameLocalized`,
          ma.`year` AS `year`
          FROM `movies_awards` ma
          INNER JOIN `awards` a
          ON ma.`award_id` = a.`award_id`
          WHERE ma.`movie_id` = ?
          ORDER BY `nameLocalized` ASC, `name` ASC",
        "d",
        [ $this->id ]
      );
    }
    return $awards;
  }

  /**
   * Retrieve the movie's cast from the database.
   *
   * @staticvar array $cast
   * @return array
   *  A keyed array containing the cast information in an associative array.
   */
  public function getCast() {
    static $cast = null;
    if ($cast === null) {
      $cast = $this->select(
        "SELECT
          p.`person_id` AS `id`,
          p.`name` AS `name`,
          p.`deleted` AS `deleted`,
          mc.`roles`
          FROM `movies_cast` mc
          INNER JOIN `persons` p
          ON mc.`person_id` = p.`person_id`
          WHERE mc.`movie_id` = ?
          ORDER BY `name` ASC",
        "d",
        [ $this->id ]
      );
      settype($cast["deleted"], "boolean");
    }
    return $cast;
  }

  /**
   * Retrieve the movie's crew from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $crew
   * @return array
   *  A keyed array containing the crew information in an associative array.
   */
  public function getCrew() {
    global $i18n;
    static $crew = null;
    if ($crew === null) {
      $crew = $this->select(
        "SELECT
          mc.`crew_id` AS `crewId`,
          p.`person_id` AS `personId`,
          p.`name` AS `personName`,
          p.`deleted` AS `personDeleted`,
          c.`company_id` AS `companyId`,
          c.`name` AS `companyName`,
          c.`deleted` AS `companyDeleted`,
          j.`job_id` AS `jobId`,
          j.`title` AS `jobTitle`,
          COLUMN_GET(j.`dyn_titles`, '{$i18n->languageCode}' AS BINARY) AS `jobTitleLocalized`
          FROM `movies_crew` mc
          INNER JOIN `jobs` j
            ON mc.`job_id` = j.`job_id`
          LEFT JOIN `persons` p
            ON mc.`person_id` = p.`person_id`
          LEFT JOIN `companies` c
            ON mc.`company_id` = c.`company_id`
          WHERE mc.`movie_id` = ?
          ORDER BY `personName` ASC, `companyName` ASC",
        "d",
        [ $this->id ]
      );
      settype($crew["personDeleted"], "deleted");
      settype($crew["companyDeleted"], "deleted");
    }
    return $crew;
  }

  /**
   * Retrieve the movie's production countries from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $countries
   * @return array
   *  A keyed array containing the country information in an associative array.
   */
  public function getCountries() {
    global $i18n;
    static $countries = null;
    if ($countries === null) {
      $countries = $this->select(
        "SELECT
          c.`country_id` AS `id`,
          c.`iso_alpha-2` AS `isoCode`,
          c.`name` AS `name`,
          COLUMN_GET(c.`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS `nameLocalized`
          FROM `movies_countries` mc
          INNER JOIN `countries` c
          ON mc.`country_id` = c.`country_id`
          WHERE mc.`movie_id` = ?
          ORDER BY `nameLocalized` ASC, `name` ASC",
        "d",
        [ $this->id ]
      );
    }
    return $countries;
  }

  /**
   * Retrieve the movie's directors from the database.
   *
   * @staticvar array $directors
   * @return array
   *  A keyed array containing the director information in an associative array.
   */
  public function getDirectors() {
    static $directors = null;
    if ($directors === null) {
      $directors = $this->select(
        "SELECT
          p.`person_id` AS `id`,
          p.`name` AS `name`,
          p.`deleted` AS `deleted`
          FROM `movies_directors` md
          INNER JOIN `persons` p
          ON md.`person_id` = p.`person_id`
          WHERE md.`movie_id` = ?
          ORDER BY `name` ASC",
        "d",
        [$this->id]
      );
      settype($directors["deleted"], "boolean");
    }
    return $directors;
  }

  /**
   * Retrieve the movie's genres from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $genres
   * @return array
   *  A keyed array containing the genre information in an associative array.
   */
  public function getGenres() {
    global $i18n;
    static $genres = null;
    if ($genres === null) {
      $genres = $this->select(
        "SELECT
          g.`genre_id` AS `id`,
          g.`name` AS `name`,
          COLUMN_GET(g.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `nameLocalized`
          FROM `movies_genres` mg
          INNER JOIN `genres` g
          ON mg.`genre_id` = g.`genre_id`
          WHERE mg.`movie_id` = ?
          ORDER BY `nameLocalized` ASC, `name` ASC",
        "d",
        [ $this->id ]
      );
    }
    return $genres;
  }

  /**
   * Retrieve the movie's languages from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $languages
   * @return array
   *  A keyed array containing the language information in an associative array.
   */
  public function getLanguages() {
    global $i18n;
    static $languages = null;
    if ($languages === null) {
      $languages = $this->select(
        "SELECT
          l.`language_id` AS `id`,
          l.`iso_alpha-2` AS `isoCode`,
          l.`name` AS `name`,
          COLUMN_GET(l.`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS `nameLocalized`
          FROM `movies_languages` ml
          INNER JOIN `languages` l
          ON ml.`language_id` = l.`language_id`
          WHERE ml.`movie_id` = ?
          ORDER BY `nameLocalized` ASC, `name` ASC",
        "d",
        [ $this->id ]
      );
    }
    return $languages;
  }

  /**
   * Retrieve the movie's external links from the database.
   *
   * @staticvar array $links
   * @return array
   *  A keyed array containing the link information in an associative array.
   */
  public function getLinks () {
    static $links = null;
    if ($links === null) {
      $links = $this->select(
        "SELECT
          ml.`title` AS `title`,
          ml.`text` AS `text`,
          ml.`url` AS `URL`,
          l.`language_id` AS `languageId`,
          l.`iso_alpha-2` AS `languageIsoCode`,
          l.`name` AS `languageName`
          FROM `movies_links` ml
          INNER JOIN `languages` l
          ON ml.`language_id` = l.`language_id`
          WHERE ml.`movie_id` = ?",
        "d",
        [ $this->id ]);
    }
    return $links;
  }


  public function getPosters() {
    global $i18n;
    static $posters = null;
    if ($posters === null) {
      $posters = $this->select(
        "SELECT
          `poster_id` AS `posterId`,
          `country_id` AS `country`,
          `filename`,
          `width`,
          `height`,
          `size`,
          `ext`,
          COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description`
          FROM `posters`
          WHERE `movie_id` = ?
          ORDER BY rating DESC",
        "d",
        [ $this->id ]);
      $count = count($posters);
      for ($i = 0; $i < $count; ++$i) {
        if (isset($posters[$i]["country"])) {
          $posters[$i]["country"] = $i18n->getCountries()["id"][$posters[$i]["country"]]["name"];
        }
      }
    }
    return $posters;
  }

  /**
   * Retrieve the movie's relationships to other movies.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $relationships
   * @return array
   *  A keyed array containing the relationship information to other movies in an associative array.
   */
  public function getRelationships() {
    global $i18n;
    static $relationships = null;
    if ($relationships === null) {
      $movieIds = [];
      foreach ($this->relationships as $rel) {
        if (isset($rel["movie_id"])) {
          $movieIds[] = $rel["movie_id"];
        }
      }
      $relationships = $this->select(
        "SELECT
          m.`original_title` AS `originalTitle`,
          m.`year` AS `year`,
          mt.`title`
          FROM `movies_titles` mt
          INNER JOIN `movies` m
          ON mt.`movie_id` = m.`movie_id`
          WHERE m.`movie_id` IN (" . implode(",", $movieIds). ")
            AND mt.`is_display_title` = 1
            AND mt.`language_id` = ?",
        "dd",
        [ $i18n->getLanguages()["code"]["id"] ]
      );

    }
    return $relationships;
  }

  /**
   * Retrieve the movie's styles from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $styles
   * @return array
   *  A keyed array containing the style information in an associative array.
   */
  public function getStyles() {
    global $i18n;
    static $styles = null;
    if ($styles === null) {
      $styles = $this->select(
        "SELECT
          s.`style_id` AS `id`,
          s.`name` AS `name`,
          COLUMN_GET(s.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `nameLocalized`
          FROM `movies_styles` ms
          INNER JOIN `styles` s
          ON ms.`style_id` = s.`style_id`
          WHERE ms.`movie_id` = ?
          ORDER BY `nameLocalized` ASC, `name` ASC",
        "d",
        [ $this->id ]
      );
    }
    return $styles;
  }

  /**
   * Retrieve the movie's taglines from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $tagLines
   * @return array
   *  A keyed array containing the tagline information in an associative array.
   */
  public function getTagLines() {
    global $i18n;
    static $tagLines = null;
    if ($tagLines === null) {
      $tagLines = $this->select(
        "SELECT
          mt.`tagline` AS `tagline`,
          l.`language_id` AS `languageId`,
          l.`iso_alpha-2` AS `languageIsoCode`,
          l.`name` AS `languageName`,
          COLUMN_GET(l.`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS `languageNameLocalized`
          FROM `movies_taglines` mt
          INNER JOIN `languages` l
          ON mt.`language_id` = l.`language_id`
          WHERE mt.`movie_id` = ?",
          "d",
          [$this->id]
      );
    }
    return $tagLines;
  }

  /**
   * Retrieve the movie's titles from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @staticvar array $titles
   * @return array
   *  A keyed array containing the title information in an associative array.
   */
  public function getTitles() {
    global $i18n;
    static $titles = null;
    if ($titles === null) {
      $titles = $this->select(
        "SELECT mt.`title` AS `title`,
          COLUMN_GET(`dyn_comments`, 'en' AS BINARY) AS `comment`,
          COLUMN_GET(`dyn_comments`, '{$i18n->languageCode}' AS BINARY) AS `commentLocalized`,
          mt.`is_display_title` AS isDisplayTitle,
          l.`language_id` AS `languageId`,
          l.`iso_alpha-2` AS `languageIsoCode`,
          l.`name` AS `languageName`,
          COLUMN_GET(l.`dyn_translations`, '{$i18n->languageCode}' AS BINARY) AS `languageNameLocalized`
          FROM `movies_titles` mt
          INNER JOIN `languages` l
          ON mt.`language_id` = l.`language_id`
          WHERE mt.`movie_id` = ?
          ORDER BY `title` ASC",
        "d",
        [ $this->id ]
      );
      $count = count($titles);
      for($i = 0; $i < $count; ++$i) {
        settype($titles[$i][""], "boolean");
      }
    }
    return $titles;
  }

}
