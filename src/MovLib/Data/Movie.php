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
namespace MovLib\Data;

use \MovLib\Data\Image\Movie as MovieImage;
use \MovLib\Exception\MovieException;

/**
 * The movie is responsible for all database related functionality of a single movie entry.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Movie extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's id.
   *
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
   *
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
   * The movie's created timestamp.
   *
   * @var int
   */
  public $created;


  // ------------------------------------------------------------------------------------------------------------------- Properties from other tables


  /**
   * Sorted numeric array containing the movie's genres with the ID and localized name of the genre as associative array.
   *
   * @var array
   */
  private $genres;

  /**
   * Numeric array containing the movie's language information as associative array.
   *
   * @var array
   */
  private $languages;

  /**
   * Numeric array containing the movie's link information as associative array.
   *
   * @var array
   */
  private $links;

  /**
   * Associative array containing movie's relationships to other movies.
   *
   * @var array
   */
  private $relationships;

  /**
   * Sorted numeric array containing the movie's style information (ID and localized name) as associative array.
   *
   * @var array
   */
  private $styles;

  /**
   * Numeric array containing the movie's tagline information as associative array.
   *
   * @var array
   */
  private $taglines;

  /**
   * Numeric array containing the movie's title information as associative array.
   *
   * @var array
   */
  private $titles;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new single movie.
   *
   * Construct new movie from given ID and gather all movie information available in the movies table. If no ID
   * is supplied, the movie will be initialized with the <code>$properties</code> array.
   * If neither ID nor the properties are supplied, an empty movie will be created.
   * If the ID is invalid a <code>\MovLib\Exception\MovieException</code> will be thrown.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $id [optional]
   *   The movie's ID to construct this model from.
   * @param array $properties [optional]
   *   Associative array of properties to initialize the movie with.
   * @throws \MovLib\Exception\MovieException
   */
  public function __construct($id = null, array $properties = null) {
    global $i18n;
    if (isset($id)) {
      $properties = $this->select(
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
          UNIX_TIMESTAMP(`created`) AS `created`
        FROM `movies`
        WHERE `movie_id` = ?
          LIMIT 1",
        "d",
        [ $id ]
      );
      if (isset($properties[0]) === false) {
        throw new MovieException("Could not find movie with ID '{$id}'!");
      }
      $properties = $properties[0];
    }
    if (isset($properties)) {
      foreach ($properties as $k => $v) {
        $this->{$k} = $v;
      }
      settype($this->deleted, "boolean");
    }
  }

  /**
   * Get the movie's awards.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the award information as associative array.
   */
  public function getAwards() {
    global $i18n;
    $dbAwards = $this->select(
      "SELECT
        a.`award_id` AS `id`,
        a.`name` AS `name`,
        COLUMN_GET(a.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`,
        `ma`.`award_count` AS `award_count`,
        `ma`.`year` AS `year`,
        `ma`.`won` AS `won`
      FROM `movies_awards` ma
        INNER JOIN `awards` a
          ON ma.`award_id` = a.`award_id`
      WHERE ma.`movie_id` = ?
        ORDER BY `name` ASC",
      "d",
      [ $this->id ]
    );
    if (($c = count($dbAwards)) > 0) {
      $awardSort = [];
      for ($i = 0; $i < $c; ++$i) {
        settype($dbAwards[$i]["won"], "boolean");
        $dbAwards[$i]["name"] = empty($dbAwards[$i]["name_localized"]) ? $dbAwards[$i]["name"] : $dbAwards[$i]["name_localized"];
        $awardSort["{$dbAwards[$i]["name"]}{$dbAwards[$i]["award_count"]}"] = $dbAwards[$i];
      }
      $i18n->getCollator()->ksort($awardSort);
      return array_values($awardSort);
    }
    return [];
  }

  /**
   * Get the movie's cast.
   *
   * @todo Move to \MovLib\Data\Persons
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the cast information as associative array.
   */
  public function getCast() {
    global $i18n;
    $dbCast = $this->select(
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
    $c = count($dbCast);
    $castSort = [];
    for ($i = 0; $i < $c; ++$i){
      settype($dbCast[$i]["deleted"], "boolean");
      $castSort["{$dbCast[$i]["name"]}{$dbCast[$i]["id"]}"] = $dbCast[$i];
    }
    $i18n->getCollator()->ksort($castSort);
    return array_values($castSort);
  }

  /**
   * Get the movie's crew.
   *
   * @todo Move to \MovLib\Data\Crew
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the crew information as associative array.
   */
  public function getCrew() {
    global $i18n;
      $dbCrew = $this->select(
        "SELECT
          `mc`.`crew_id` AS `crew_id`,
          `p`.`person_id` AS `person_id`,
          `p`.`name` AS `person_name`,
          `p`.`deleted` AS `person_deleted`,
          `c`.`company_id` AS `company_id`,
          `c`.`name` AS `company_name`,
          `c`.`deleted` AS `company_deleted`,
          `j`.`job_id` AS `job_id`,
          `j`.`title` AS `job_title`,
          COLUMN_GET(`j`.`dyn_titles`, ? AS BINARY) AS `job_title_localized`
        FROM `movies_crew` mc
          INNER JOIN `jobs` `j`
            ON `mc`.`job_id` = `j`.`job_id`
          LEFT JOIN `persons` `p`
            ON `mc`.`person_id` = `p`.`person_id`
          LEFT JOIN `companies` `c`
            ON `mc`.`company_id` = `c`.`company_id`
        WHERE `mc`.`movie_id` = ?
          ORDER BY `person_name` ASC, `company_name` ASC",
        "sd",
        [ $i18n->languageCode, $this->id ]
      );
      $c = count($dbCrew);
      $crewSort = [];
      for ($i = 0; $i < $c; ++$i){
        $dbCrew[$i]["job_title"] = $dbCrew[$i]["job_title_localized"] ?: $dbCrew[$i]["job_title"];
        settype($dbCrew[$i]["person_deleted"], "boolean");
        settype($dbCrew[$i]["company_deleted"], "boolean");
        $crewSort["{$dbCrew[$i]["job_title"]}{$dbCrew[$i]["job_id"]}"] = $dbCrew[$i];
      }
      $i18n->getCollator()->ksort($crewSort);
      return array_values($crewSort);
  }

  /**
   * Get the movie's countries.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Sorted numeric array containing the ID, ISO code and localized name of the country as associative array.
   */
  public function getCountries() {
    global $i18n;
    $countryIds = $this->select("SELECT `country_id` FROM `movies_countries` WHERE `movie_id` = ?", "d", [ $this->id ]);
    if (($c = count($countryIds)) > 0) {
      $i18nCountries = $i18n->getCountries();
      $countrySort = [];
      for ($i = 0; $i < $c; ++$i) {
        $countrySort[ $i18nCountries[ $countryIds[$i]["country_id"] ]["name"] ] = $i18nCountries[ $countryIds[$i]["country_id"] ];
      }
      $i18n->getCollator()->ksort($countrySort);
      return array_values($countrySort);
    }
    else {
      return [];
    }
  }

  /**
   * Get the movie's directors.
   *
   * @todo Move to \MovLib\Data\Persons
   * @return array
   *   Numeric array containing the director information as associative array.
   */
  public function getDirectors() {
    $directorsData = $this->select(
      "SELECT
        p.`person_id` AS `id`,
        p.`name` AS `name`,
        p.`deleted` AS `deleted`,
        pp.`image_id`,
        pp.`filename` AS `filename`,
        pp.`ext` AS `ext`
      FROM `movies_directors` md
        INNER JOIN `persons` p
          ON md.`person_id` = p.`person_id`
        LEFT JOIN `persons_photos` pp
          ON p.`person_id` = pp.`person_id`
      WHERE md.`movie_id` = ?
        ORDER BY `name` ASC, pp.`upvotes` DESC",
      "d",
      [$this->id]
    );
    $count = count($directorsData);
    $usedIds = [];
    for ($i = 0; $i < $count; ++$i) {
      if (!in_array($directorsData[$i]["id"], $usedIds)) {
        settype($directorsData[$i]["deleted"], "boolean");
        $this->directors[] = $directorsData[$i];
        $usedIds[] = $directorsData[$i]["id"];
      }
    }
  }

  /**
   * Get the movie's genres.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getGenres() {
    global $i18n;
    $dbGenres = $this->select(
      "SELECT
        `g`.`genre_id`,
        `g`.`name`,
        COLUMN_GET(`g`.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`
      FROM `movies_genres` `mg`
        INNER JOIN `genres` `g`
          ON `mg`.`genre_id` = `g`.`genre_id`
      WHERE `mg`.`movie_id` = ?",
      "d", [ $this->id ]
    );
    if (($c = count($dbGenres))) {
      $tmpGenres = [];
      for ($i = 0; $i < $c; ++$i) {
        $dbGenres[$i]["name"] = $dbGenres[$i]["name_localized"] ?: $dbGenres[$i]["name"];
        $tmpGenres[$dbGenres[$i]["name"]] = $dbGenres[$i];
      }
      return array_values($tmpGenres);
    }
    return [];
  }

  /**
   * Get the movie's languages.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the language information as associative array.
   */
  public function getLanguages() {
    global $i18n;
    if (!$this->languages) {
      $this->languages = [];
      $result = $this->select("SELECT `language_id` AS `id` FROM `movies_languages` WHERE `movie_id` = ?", "d", [ $this->id ]);
      if (($c = count($result))) {
        $i18nLanguages = $i18n->getLanguages();
        $tmpLanguages = [];
        for ($i = 0; $i < $c; ++$i) {
          $tmpLanguages[ $result[$i]["language_id"] ] = $i18nLanguages[ $result[$i]["language_id"] ]["name"];
        }
        $i18n->getCollator()->asort($tmpLanguages);
        foreach ($tmpLanguages as $id => $name) {
          $this->languages[] = $i18nLanguages[$id];
        }
      }
    }
    return $this->languages;
  }

  /**
   * Get the movie's links.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the link information as associative array.
   */
  public function getLinks () {
    global $i18n;
    if (!$this->links) {
      $this->links = $this->select(
        "SELECT
          `title` AS `title`,
          `text` AS `text`,
          `url` AS `URL`,
          `language_id` AS `language`
        FROM `movies_links`
        WHERE `movie_id` = ?",
        "d",
        [ $this->id ]
      );
      if (($c = count($this->links))) {
        $i18nLanguages = $i18n->getLanguages();
        for ($i = 0; $i < $c; ++$i) {
          $this->links[$i]["language"] = $i18nLanguages[ $this->links[$i]["language"] ];
        }
      }
    }
    return $this->links;
  }

  /**
   * Get the movie's display poster.
   *
   * @param string $movieTitle [optional]
   *   The movie title for the alt attribute.
   * @return \MovLib\Data\MovieImage
   *   The movie's display poster.
   */
  public function getDisplayPoster($movieTitle = "") {
    $posterId = $this->select("SELECT `image_id` AS `id` FROM `movies_images` WHERE `movie_id` = ? AND `type` = ? ORDER BY `upvotes` DESC LIMIT 1", "di", [ $this->id, MovieImage::IMAGETYPE_POSTER ]);
    $displayPoster = new MovieImage($this->id, MovieImage::IMAGETYPE_POSTER, empty($posterId[0]["id"]) ? null : $posterId[0]["id"], $movieTitle);
    return $displayPoster;
  }

  /**
   * Get the movie's relationships to other movies.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Associative array containing the relationship information to other movies as associative array.
   */
  public function getRelationships() {
    global $i18n;
    if (!$this->relationships) {
      throw new \MovLib\Exception\DebugException("Implement the relationship query for movies!");
    }
    return $this->relationships;
  }

  /**
   * Get the movie's styles.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getStyles() {
    global $i18n;
    if (!$this->styles) {
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
      if (($c = count($result))) {
        $tmpStyles = [];
        for ($i = 0; $i < $c; ++$i) {
          if (!empty($result[$i]["name_localized"])) {
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
   * Get the movie's taglines.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the tagline information as associative array.
   */
  public function getTagLines() {
    global $i18n;
    if (!$this->taglines) {
      $this->taglines = $this->select(
        "SELECT
          `tagline` AS `tagline`,
          `language_id` AS `language`
        FROM `movies_taglines`
        WHERE `movie_id` = ?",
        "d",
        [ $this->id ]
      );
      if (($c = count($this->taglines))) {
        $i18nLanguages = $i18n->getLanguages();
        for ($i = 0; $i < $c; ++$i) {
          $this->taglines[$i]["language"] = $i18nLanguages[ $this->taglines[$i]["language"] ];
        }
      }
    }
    return $this->taglines;
  }

  /**
   * Get the movie's display title.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The display title of this movie with fallback to the original title.
   */
  public function getDisplayTitle() {
    global $i18n;
    $displayTitle = $this->select(
      "SELECT `title` FROM `movies_titles` WHERE `movie_id` = ? AND is_display_title = true AND `language_id` = ? ORDER BY `title` ASC LIMIT 1",
      "di", [ $this->id, $i18n->getLanguages(I18n::KEY_CODE)[$i18n->languageCode][I18n::KEY_ID] ]
    );
    if (empty($displayTitle)) {
      return $this->originalTitle;
    }
    return $displayTitle[0]["title"];
  }

  /**
   * Get the movie's titles.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the title information as associative array.
   */
  public function getTitles() {
    global $i18n;
    if (!$this->titles) {
      $this->titles = $this->select(
        "SELECT `title` AS `title`,
          COLUMN_GET(`dyn_comments`, 'en' AS BINARY) AS `comment`,
          COLUMN_GET(`dyn_comments`, '{$i18n->languageCode}' AS BINARY) AS `commentLocalized`,
          `is_display_title` AS isDisplayTitle,
          `language_id` AS `language`
        FROM `movies_titles`
        WHERE `movie_id` = ?
          ORDER BY `title` ASC",
        "d",
        [ $this->id ]
      );
      if (($c = count($this->titles))){
        $i18nLanguages = $i18n->getLanguages();
        for ($i = 0; $i < $c; ++$i) {
          $this->titles[$i]["language"] = $i18nLanguages[ $this->titles[$i]["language"] ];
          settype($this->titles[$i]["isDisplayTitle"], "boolean");
        }
      }
    }
    return $this->titles;
  }

}
