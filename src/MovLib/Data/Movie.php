<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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

use \MovLib\Data\Language;
use \MovLib\Data\Image\Movie as MovieImage;
use \MovLib\Exception\MovieException;

/**
 * The movie is responsible for all database related functionality of a single movie entry.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
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
   * The movie's official website URL.
   *
   * @var string
   */
  public $website;

  /**
   * The movie's created timestamp.
   *
   * @var int
   */
  public $created;


  // ------------------------------------------------------------------------------------------------------------------- Properties from other tables


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
   * @throws \MovLib\Exception\MovieException
   */
  public function __construct($id = null) {
    global $i18n;
    if (isset($id)) {
      $stmt = $this->query("SELECT
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
          `website`,
          UNIX_TIMESTAMP(`created`) AS `created`
        FROM `movies`
        WHERE `movie_id` = ?
          LIMIT 1",
        "d",
        [ $id ]
      );
      $stmt->bind_result(
        $this->id,
        $this->originalTitle,
        $this->rating,
        $this->meanRating,
        $this->votes,
        $this->deleted,
        $this->year,
        $this->runtime,
        $this->rank,
        $this->synopsis,
        $this->website,
        $this->created
      );
      $stmt->fetch();
      if (!$this->id) {
        throw new MovieException("No movie found for ID '{$id}'!");
      }
      $this->deleted = (boolean) $this->deleted;
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
    $stmt = $this->query(
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
    $dbAwards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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
    $stmt = $this->query(
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
    $dbCast = $stmt->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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
      $stmt = $this->query(
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
      $dbCrew = $stmt->fetch_all(MYSQLI_ASSOC);
      $stmt->close();
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
   * @todo Move to \MovLib\Data\Countries
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Data\Countries
   *   The countries.
   */
  public function getCountries() {
    global $i18n;
    $name = $i18n->languageCode == $i18n->defaultLanguageCode
      ? "`country`.`name`"
      : "COLUMN_GET(`country`.`dyn_translations`, '{$i18n->languageCode}' AS BINARY)"
    ;
    $result = $this->query(
      "SELECT
        `country`.`id`,
        {$name} AS `name`,
        `country`.`code`
      FROM `countries` AS `country`
        INNER JOIN `movies_countries` `m` ON `m`.`country_id` = `country`.`id`
      WHERE `m`.`movie_id` = ?
      ORDER BY `country`.`id`",
      "d",
      [ $this->id ]
    )->get_result();
    $countries = [];
    while ($country = $result->fetch_object("\\MovLib\\Data\\Country")) {
      $countries[$country->name] = $country;
    }
    $i18n->getCollator()->ksort($countries);
    return $countries;
  }

  /**
   * Get the movie's directors.
   *
   * @todo Move to \MovLib\Data\Persons
   * @return array
   *   Numeric array containing the director information as associative array.
   */
  public function getDirectors() {
    $stmt = $this->query(
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
    $directorsData = $stmt->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
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
   * @todo Move to \MovLib\Data\Genres
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getGenres() {
    global $i18n;
    $dbGenres = $this->query(
      "SELECT
        `g`.`genre_id`,
        `g`.`name`,
        COLUMN_GET(`g`.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`
      FROM `movies_genres` `mg`
        INNER JOIN `genres` `g`
          ON `mg`.`genre_id` = `g`.`genre_id`
      WHERE `mg`.`movie_id` = ?",
      "d", [ $this->id ]
    )->get_result()->fetch_all(MYSQLI_ASSOC);
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
   * @todo Move to \MovLib\Data\Languages
   *       NO!
   *       This is a specialized query, specialized for movies, this has nothing to do with general languages!
   *       Please think in object oriented ways and don't let yourself be fooled just because it contains the word "Language"!
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Numeric array containing the language information as associative array.
   */
  public function getLanguages() {
    global $i18n;
    $name = $i18n->languageCode == $i18n->defaultLanguageCode
      ? "`language`.`name`"
      : "COLUMN_GET(`language`.`dyn_translations`, '{$i18n->languageCode}' AS BINARY)"
    ;
    $result = $this->query(
      "SELECT
        `language`.`id`,
        {$name} AS `name`,
        `language`.`code`
      FROM `languages` AS `language`
        INNER JOIN `movies_languages` AS `m` ON `m`.`language_id` = `language`.`id`
      WHERE `m`.`movie_id` = ?
      ORDER BY `language`.`language_id`",
      "d",
      [ $this->id ]
    )->get_result();
    $languages = [];
    while ($language = $result->fetch_object("\\MovLib\\Data\\Language")) {
      $languages[$language->name] = $language;
    }
    $i18n->getCollator()->ksort($languages);
    return $languages;
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
    $posterId = $this->query("SELECT `image_id` AS `id` FROM `movies_images` WHERE `movie_id` = ? AND `type` = ? ORDER BY `upvotes` DESC LIMIT 1", "di", [ $this->id, MovieImage::IMAGETYPE_POSTER ])->get_result()->fetch_assoc();
    $displayPoster = new MovieImage($this->id, MovieImage::IMAGETYPE_POSTER, empty($posterId["id"]) ? null : $posterId["id"], $movieTitle);
    return $displayPoster;
  }

  /**
   * Get the movie's relationships to other movies.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Data\Languages
   *   The Languages.
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
   * @todo Move to \MovLib\Data\Styles
   *       YES! Styles are specific to movies, this could be moved, but it's better to create a \MovLib\Data\Movie\Full
   *       class that directly fetches all data it needs rather than creating many small objects. It's better to have
   *       specialized classes rather than many classes that are only usable after composing them together. Of course,
   *       if we think in pure object oriented ways, this would by correct, but also consider performance. Styles are
   *       a special case and we might have to have a close look on how to design this (or taglines).
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getStyles() {
    global $i18n;
    $dbStyles = $this->query(
      "SELECT
        `s`.`style_id` AS `id`,
        `s`.`name` AS `name`,
        COLUMN_GET(`s`.`dyn_names`, ? AS BINARY) AS `name_localized`
      FROM `movies_styles` `ms`
        INNER JOIN `styles` `s` ON `ms`.`style_id` = `s`.`style_id`
      WHERE `ms`.`movie_id` = ?",
      "sd",
      [ $i18n->languageCode, $this->id ]
    )->get_result()->fetch_all(MYSQLI_ASSOC);
    if (!$dbStyles) {
      return [];
    }
    $c = count($dbStyles);
    $tmpStyles = [];
    for ($i = 0; $i < $c; ++$i) {
      $dbStyles[$i]["name"] = $dbStyles[$i]["name_localized"] ?: $dbStyles[$i]["name"];
      $tmpStyles[ $dbStyles[$i]["name"] ] = $dbStyles[$i];
    }
    $i18n->getCollator()->ksort($tmpStyles);
    return array_values($tmpStyles);
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
    // @todo Directly join with languages!
    if (($dbTagLines = $this->query("SELECT `tagline` AS `tagline`, `language_id` AS `language` FROM `movies_taglines` WHERE `movie_id` = ?", "d", [ $this->id ])->get_result()->fetch_all(MYSQLI_ASSOC))) {
      $c = count($dbTagLines);
      $i18nLanguages = (new Languages())->orderById();
      $tagLinesSort = [];
      for ($i = 0; $i < $c; ++$i) {
        $dbTagLines[$i]["language"] = $i18nLanguages[ $dbTagLines[$i]["language"] ];
        $tagLinesSort["{$dbTagLines[$i]["tagline"]}{$dbTagLines[$i]["language"]["id"]}"] = $dbTagLines[$i];
      }
      $i18n->getCollator()->ksort($tagLinesSort);
      return array_values($tagLinesSort);
    }
    return [];
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
    $stmt = $this->query(
      "SELECT `title` FROM `movies_titles` WHERE `movie_id` = ? AND is_display_title = true AND `language_id` = ? ORDER BY `title` ASC LIMIT 1",
      "di", [ $this->id, (new Language(Language::FROM_CODE, $i18n->languageCode))->id ]
    );
    $stmt->bind_result($displayTitle);
    if ($stmt->fetch() === false) {
      throw new MovieException("Error fetching the display title for movie {$this->id}.");
    }
    if (empty($displayTitle)) {
      return $this->originalTitle;
    }
    return $displayTitle;
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
    $result = $this->query(
      "SELECT
        `title` AS `title`,
        COLUMN_GET(`dyn_comments`, ? AS BINARY) AS `comment`,
        `is_display_title`,
        `language_id` AS `language`
      FROM `movies_titles`
        WHERE `movie_id` = ?",
      "sd",
      [ $i18n->languageCode, $this->id ]
    )->get_result();
    $dbTitles = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
    if (($c = count($dbTitles))) {
      $i18nLanguages = $i18n->getLanguages();
      $titlesSorted  = [];
      for ($i = 0; $i < $c; ++$i) {
        $dbTitles[$i]["language"] = $i18nLanguages[ $dbTitles[$i]["language"] ];
        settype($dbTitles[$i]["is_display_title"], "boolean");
        $titlesSorted["{$dbTitles[$i]["title"]}{$dbTitles[$i]["language"]["id"]}"] = $dbTitles[$i];
      }
      $i18n->getCollator()->ksort($titlesSorted);
      return array_values($titlesSorted);
    }
    return [];
  }

}
