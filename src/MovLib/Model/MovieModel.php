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

use \MovLib\Exception\ErrorException;
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

  // ------------------------------------------------------------------------------------------------------------------- Properties from other tables

  /**
   * A keyed array containing the movie's award information in an associative array.
   * @var null|array
   */
  private $awards = null;

  /**
   * A keyed array containing the movie's cast information in an associative array.
   * @var null|array
   */
  private $cast = null;

  /**
   * A keyed array containing the movie's crew information in an associative array.
   * @var null|array
   */
  private $crew = null;

  /**
   * Sorted numeric array containing the movie's countries as associative arrays with the ID, the ISO code and localized name of the country.
   * @var null|array
   */
  private $countries = null;

  /**
   * A keyed array containing the movie's director information in an associative array.
   * @var null|array
   */
  private $directors = null;

  /**
   * Sorted numeric array containing the movie's genres with the ID and localized name of the genre as associative array.
   * @var null|array
   */
  private $genres = null;

  /**
   * A keyed array containing the movie's language information in an associative array.
   * @var null|array
   */
  private $languages = null;

  /**
   * A keyed array containing the movie's link information in an associative array.
   * @var null|array
   */
  private $links = null;

  /**
   * Associative array containing the information of the movie's display poster.
   * @var null|array
   */
  private $displayPoster = null;

  /**
   * Sorted numeric array containing the movie's poster information as an associative array.
   * @var null|array
   */
  private $posters = null;

  /**
   * Sorted numeric array containing the movie's style information (ID and localized name) as associative array.
   * @var null|array
   */
  private $styles = null;

  /**
   * A keyed array containing the movie's tagline information in an associative array.
   * @var null|array
   */
  private $taglines = null;

  /**
   * A keyed array containing the movie's title information in an associative array.
   * @var null|array
   */
  private $titles = null;

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
   * @return array
   *  A keyed array containing the award information in an associative array.
   */
  public function getAwards() {
    global $i18n;
    if ($this->awards === null) {
      $this->awards = $this->select(
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
    return $this->awards;
  }

  /**
   * Retrieve the movie's cast from the database.
   *
   * @return array
   *  A keyed array containing the cast information in an associative array.
   */
  public function getCast() {
    if ($this->cast === null) {
      $this->cast = $this->select(
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
      $c = count($this->cast);
      for ($i = 0; $i < $c; ++$i){
        settype($this->cast[$i]["deleted"], "boolean");
      }
    }
    return $this->cast;
  }

  /**
   * Retrieve the movie's crew from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @return array
   *  A keyed array containing the crew information in an associative array.
   */
  public function getCrew() {
    global $i18n;
    if ($this->crew === null) {
      $this->crew = $this->select(
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
      $c = count($this->crew);
      for ($i = 0; $i < $c; ++$i){
        settype($this->crew[$i]["personDeleted"], "deleted");
        settype($this->crew[$i]["companyDeleted"], "deleted");
      }
    }
    return $this->crew;
  }

  /**
   * Get the movie's countries
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   * @return array
   *   Sorted numeric array containing the ID, the ISO code and localized name of the country as associative array.
   */
  public function getCountries() {
    global $i18n;
    if ($this->countries === null) {
      $this->countries = [];
      $result = $this->select("SELECT `country_id` FROM `movies_countries` WHERE `movie_id` = ?", "d", [ $this->id ]);
      $c = count($result);
      if ($c > 0) {
        $i18nCountries = $i18n->getCountries();
        $tmpCountries = [];
        for ($i = 0; $i < $c; ++$i) {
          $tmpCountries[ $result[$i]["country_id"] ] = $i18nCountries[ $result[$i]["country_id"] ]["name"];
        }
        $i18n->getCollator()->asort($tmpCountries);
        foreach ($tmpCountries as $id => $name) {
          $this->countries[] = $i18nCountries[$id];
        }
      }
    }
    return $this->countries;
  }

  /**
   * Retrieve the movie's directors from the database.
   *
   * @return array
   *  A keyed array containing the director information in an associative array.
   */
  public function getDirectors() {
    if ($this->directors === null) {
      $directorsData = $this->select(
        "SELECT
          p.`person_id` AS `id`,
          p.`name` AS `name`,
          p.`deleted` AS `deleted`,
          pp.`photo_id` AS `photo_id`,
          pp.`filename` AS `filename`,
          pp.`ext` AS `ext`
          FROM `movies_directors` md
            INNER JOIN `persons` p
            ON md.`person_id` = p.`person_id`
            LEFT JOIN `persons_photos` pp
            ON p.`person_id` = pp.`person_id`
          WHERE md.`movie_id` = ?
          ORDER BY `name` ASC, pp.`rating` DESC",
        "d",
        [$this->id]
      );
      $count = count($directorsData);
      $usedIds = [];
      for ($i = 0; $i < $count; ++$i) {
        if (in_array($directorsData[$i]["id"], $usedIds) === false) {
          settype($directorsData[$i]["deleted"], "boolean");
          $this->directors[] = $directorsData[$i];
          $usedIds[] = $directorsData[$i]["id"];
        }
      }
    }
    return $this->directors;
  }

  /**
   * Get movie's genres.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getGenres() {
    global $i18n;
    if ($this->genres === null) {
      $this->genres = [];
      $result = $this->select(
        "SELECT
          `g`.`genre_id`,
          `g`.`name`,
          COLUMN_GET(`g`.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`
        FROM `movies_genres` `mg`
          INNER JOIN `genres` `g` ON `mg`.`genre_id` = `g`.`genre_id`
        WHERE `mg`.`movie_id` = ?",
        "d", [ $this->id ]
      );
      $c = count($result);
      if ($c > 0) {
        $tmpGenres = [];
        for ($i = 0; $i < $c; ++$i) {
          if (!empty($result[$i]["name_localized"])) {
            $result[$i]["name"] = $result[$i]["name_localized"];
          }
          $tmpGenres[$result[$i]["genre_id"]] = $result[$i]["name"];
        }
        $i18n->getCollator()->asort($tmpGenres);
        foreach ($tmpGenres as $id => $name) {
          $this->genres[] = [ "id" => $id, "name" => $name ];
        }
      }
    }
    return $this->genres;
  }

  /**
   * Retrieve the movie's languages from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   * @return array
   *   A keyed array containing the language information in an associative array.
   */
  public function getLanguages() {
    global $i18n;
    if ($this->languages === null) {
      $this->languages = $this->select(
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
    return $this->languages;
  }

  /**
   * Retrieve the movie's external links from the database.
   *
   * @return array
   *  A keyed array containing the link information in an associative array.
   */
  public function getLinks () {
    if ($this->links === null) {
      $this->links = $this->select(
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
    return $this->links;
  }

  public function getPosterDisplay() {
    if ($this->displayPoster === null) {
      try {
        $posterId = $this->select(
          "SELECT
            `poster_id` AS `id`
            FROM `posters`
            WHERE `movie_id` = ?
            ORDER BY rating DESC
            LIMIT 1",
          "d",
          [ $this->id ])[0]["id"];
        $this->displayPoster = new PosterModel($this->id, $posterId);
      } catch (ErrorException $e) {
        throw new \MovieException("No diplay poster for movie {$this->id}!", $e);
      }
    }
    return $this->displayPoster;
  }

    /**
   * Retrieve the movie poster data for this movie.
   *
   * @return array
   */
  public function getPosters() {
    if ($this->posters === null) {
      $posterIds = $this->select(
        "SELECT
          `poster_id` AS `id`
          FROM `posters`
          WHERE `movie_id` = ?
          ORDER BY rating DESC",
        "d",
        [ $this->id ]);
      $count = count($posterIds);
      for ($i = 0; $i < $count; ++$i) {
        $this->posters[] = new PosterModel($this->id, $posterIds[$i]["id"]);
      }
    }
    return $this->posters;
  }

  /**
   * Retrieve the movie's relationships to other movies.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @return array
   *  A keyed array containing the relationship information to other movies in an associative array.
   */
  public function getRelationships() {
    global $i18n;
    if ($this->relationships === null) {
      $movieIds = [];
      foreach ($this->relationships as $rel) {
        if (isset($rel["movie_id"])) {
          $movieIds[] = $rel["movie_id"];
        }
      }
      $this->relationships = $this->select(
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
    return $this->relationships;
  }

  /**
   * Get the movie's styles.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getStyles() {
    global $i18n;
    if ($this->styles === null) {
      $this->styles = [];
      $result = $this->select(
        "SELECT
          `s`.`style_id`,
          `s`.`name`,
          COLUMN_GET(`s`.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`
          FROM `movies_styles` `ms`
          INNER JOIN `styles` `s` ON `ms`.`style_id` = `s`.`style_id`
          WHERE `ms`.`movie_id` = ?",
        "d",
        [ $this->id ]
      );
      $c = count($result);
      if ($c > 0) {
        $tmpStyles = [];
        for ($i = 0; $i < $c; ++$i) {
          if (empty($result[$i]["name_localized"]) === false) {
            $result[$i]["name"] = $result[$i]["name_localized"];
          }
          unset($result[$i]["name_localized"]);
          $tmpStyles[ $result[$i]["style_id"] ] = $result[$i]["name"];
        }
        $i18n->getCollator()->asort($tmpStyles);
        foreach ($tmpStyles as $id => $name) {
          $this->styles[] = [ "id" => $id, "name" => $name];
        }
      }
    }
    return $this->styles;
  }

  /**
   * Retrieve the movie's taglines from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @return array
   *  A keyed array containing the tagline information in an associative array.
   */
  public function getTagLines() {
    global $i18n;
    if ($this->taglines === null) {
      $this->taglines = $this->select(
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
    return $this->taglines;
  }

  /**
   * Retrieve the movie's titles from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *  The global I18n Model instance for translations.
   * @return array
   *  A keyed array containing the title information in an associative array.
   */
  public function getTitles() {
    global $i18n;
    if ($this->id == 1) {
      $this->titles = [
        ["title" => "test", "languageId" => 42, "isDisplayTitle" => true]
      ];
    }
    if ($this->titles === null) {
      $this->titles = $this->select(
        "SELECT `title` AS `title`,
          COLUMN_GET(`dyn_comments`, 'en' AS BINARY) AS `comment`,
          COLUMN_GET(`dyn_comments`, '{$i18n->languageCode}' AS BINARY) AS `commentLocalized`,
          `is_display_title` AS isDisplayTitle,
          `language_id` AS `languageId`
          FROM `movies_titles`
          WHERE `movie_id` = ?
          ORDER BY `title` ASC",
        "d",
        [ $this->id ]
      );
      $count = count($this->titles);
      for($i = 0; $i < $count; ++$i) {
        settype($this->titles[$i]["isDisplayTitle"], "boolean");
      }
    }
    return $this->titles;
  }

}
