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
use \MovLib\Model\BaseModel;
use \MovLib\Utility\DelayedLogger;

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
class MovieModel extends BaseModel {


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
   * The movie's relationships to other movies (from the binary column).
   *
   * @var array
   */
  private $relationships;


  // ------------------------------------------------------------------------------------------------------------------- Properties from other tables


  /**
   * Numeric array containing the movie's award information as associative array.
   *
   * @var array
   */
  private $awards;

  /**
   * Numeric array containing the movie's cast information as associative array.
   *
   * @var array
   */
  private $cast;

  /**
   * Numeric array containing the movie's crew information as associative array.
   *
   * @var array
   */
  private $crew;

  /**
   * Sorted numeric array containing the movie's countries as associative arrays with ID, ISO code and localized country
   * name.
   *
   * @var array
   */
  private $countries;

  /**
   * Numeric array containing the movie's director information as associative array.
   *
   * @var array
   */
  private $directors;

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
   * Sorted numeric array containing the movie's lobby card information as <code>\MovLib\Model\MovieImageModel</code>
   * objects.
   *
   * @var array
   */
  private $lobbyCards;

  /**
   * The movie's display poster.
   *
   * @var \MovLib\Model\MoviePosterModel
   */
  private $displayPoster;

  /**
   * Sorted numeric array containing the movie's photos information as <code>\MovLib\Model\MovieImageModel</code>
   * objects.
   *
   * @var array
   */
  private $photos;

  /**
   * Sorted numeric array containing the movie's posters as <code>\MovLib\Model\MoviePosterModel</code> objects.
   *
   * @var array
   */
  private $posters;

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
   * The movie's display title.
   *
   * @var string
   */
  private $displayTitle;

  /**
   * Numeric array containing the movie's title information as associative array.
   *
   * @var array
   */
  private $titles;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new single movie model.
   *
   * Construct new movie model from given ID and gather all movie information available in the movies table. If no ID
   * is supplied, an empty model will be returned. If the ID is invalid a <code>\MovLib\Exception\MovieException</code>
   * will be thrown.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param int $id
   *   The movie's ID to construct this model from.
   * @throws \MovLib\Exception\MovieException
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
      foreach ($result[0] as $k => $v) {
        $this->{$k} = $v;
      }
      settype($this->deleted, "boolean");
      $this->relationships = igbinary_unserialize($this->relationships);
    }
  }

  /**
   * Get the movie's awards.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Numeric array containing the award information as associative array.
   */
  public function getAwards() {
    global $i18n;
    if (!$this->awards) {
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
   * Get the movie's cast.
   *
   * @return array
   *   Numeric array containing the cast information as associative array.
   */
  public function getCast() {
    if (!$this->cast) {
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
   * Get the movie's crew.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Numeric array containing the crew information as associative array.
   */
  public function getCrew() {
    global $i18n;
    if (!$this->crew) {
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
   * Get the movie's countries.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Sorted numeric array containing the ID, ISO code and localized name of the country as associative array.
   */
  public function getCountries() {
    global $i18n;
    if (!$this->countries) {
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
   * Get the movie's directors.
   *
   * @return array
   *   Numeric array containing the director information as associative array.
   */
  public function getDirectors() {
    if ($this->directors === null) {
      $directorsData = $this->select(
        "SELECT
          p.`person_id` AS `id`,
          p.`name` AS `name`,
          p.`deleted` AS `deleted`,
          pp.`section_id` AS `section_id`,
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
        if (!in_array($directorsData[$i]["id"], $usedIds)) {
          settype($directorsData[$i]["deleted"], "boolean");
          $this->directors[] = $directorsData[$i];
          $usedIds[] = $directorsData[$i]["id"];
        }
      }
    }
    return $this->directors;
  }

  /**
   * Get the movie's genres.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Sorted numeric array containing the ID and localized name of the genre as associative array.
   */
  public function getGenres() {
    global $i18n;
    if (!$this->genres) {
      $this->genres = [];
      $result = $this->select(
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
      if ($c = count($result)) {
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
   * Get the movie's languages.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Numeric array containing the language information as associative array.
   */
  public function getLanguages() {
    global $i18n;
    if (!$this->languages) {
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
   * Get the movie's links.
   *
   * @return array
   *   Numeric array containing the link information as associative array.
   */
  public function getLinks () {
    if (!$this->links) {
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
        [ $this->id ]
      );
    }
    return $this->links;
  }

  /**
   * Get the movie's lobby cards.
   *
   * @return array
   *   Numeric array containing all the movie's lobby cards as <code>\MovLib\Model\MovieImageModel</code> objects.
   */
  public function getLobbyCards() {
    if (!$this->lobbyCards) {
      $lobbyCardIds = $this->select(
        "SELECT `section_id` AS `id` FROM `movies_images` WHERE `movie_id` = ? AND `type` = 'lobby-card' ORDER BY `created` DESC",
        "d", [ $this->id ]
      );
      $c = count($lobbyCardIds);
      for ($i = 0; $i < $c; ++$i) {
        $this->lobbyCards[] = new MovieImageModel($this->id, "lobby-card", $lobbyCardIds[$i]["id"]);
      }
    }
    return $this->lobbyCards;
  }

  /**
   * Get the movie's photos.
   *
   * @return array
   *   Numeric array containing all the movie's photos as <code>\MovLib\Model\MovieImageModel</code> objects.
   */
  public function getPhotos() {
    if (!$this->photos) {
      $photoIds = $this->select(
        "SELECT `section_id` AS `id` FROM `movies_images` WHERE `movie_id` = ? AND `type` = 'photo' ORDER BY `created` DESC",
        "d", [ $this->id ]
      );
      $c = count($photoIds);
      for ($i = 0; $i < $c; ++$i) {
        $this->photos[] = new MovieImageModel($this->id, "photo", $photoIds[$i]["id"]);
      }
    }
    return $this->photos;
  }

  /**
   * Get the movie's display poster.
   *
   * @return \MovLib\Model\MoviePosterModel
   *   The movie's display poster.
   */
  public function getDisplayPoster() {
    if (!$this->displayPoster) {
      $posterId = $this->select("SELECT `section_id` AS `id` FROM `posters` WHERE `movie_id` = ? ORDER BY rating DESC LIMIT 1", "d", [ $this->id ]);
      $posterId = empty($posterId[0]["id"]) ? null : $posterId[0]["id"];
      try {
        $this->displayPoster = new MoviePosterModel($this->id, $posterId);
      } catch (ImageException $e) {
        // Could happen if a poster was deleted and a request is made to the poster while deletion is running.
        DelayedLogger::logException($e, E_NOTICE);
        $this->displayPoster = new MoviePosterModel($this->id);
      }
    }
    return $this->displayPoster;
  }

  /**
   * Get the movie's posters.
   *
   * @return array
   *   Numeric array containing all movie's posters as <code>\MovLib\Model\MoviePosterModel</code> objects.
   */
  public function getPosters() {
    if (!$this->posters) {
      $posterIds = $this->select("SELECT `section_id` AS `id` FROM `posters` WHERE `movie_id` = ? ORDER BY `created` DESC", "d", [ $this->id ]);
      $c = count($posterIds);
      for ($i = 0; $i < $c; ++$i) {
        try {
          $this->posters[] = new MoviePosterModel($this->id, $posterIds[$i]["id"]);
        } catch (ImageException $e) {
          // Could happen if a poster was deleted and a request is made to the poster while deletion is running.
          DelayedLogger::logException($e, E_NOTICE);
        }
      }
    }
    return $this->posters;
  }

  /**
   * Get the movie's relationships to other movies.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Numeric array containing the relationship information to other movies as associative array.
   */
  public function getRelationships() {
    global $i18n;
    if (!$this->relationships) {
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
      if ($c = count($result)) {
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
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   Numeric array containing the tagline information as associative array.
   */
  public function getTagLines() {
    global $i18n;
    if (!$this->taglines) {
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
        [ $this->id ]
      );
    }
    return $this->taglines;
  }

  /**
   * Get the movie's display title.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return string
   *   The display title of this movie with fallback to the original title.
   */
  public function getDisplayTitle() {
    global $i18n;
    if (!$this->displayTitle) {
      $displayTitle = $this->select(
        "SELECT `title` AS `title` FROM `movies_titles` WHERE `movie_id` = ? AND is_display_title = 1 AND `language_id` = ? ORDER BY `title` ASC LIMIT 1",
        "di", [ $this->id, $i18n->getLanguages(I18nModel::KEY_CODE)[$i18n->languageCode]["id"] ]
      );
      $this->displayTitle = empty($displayTitle) ? $this->originalTitle : $displayTitle[0]["title"];
    }
    return $this->displayTitle;
  }

  /**
   * Get the movie's titles.
   *
   * @global \MovLib\Model\I18nModel $i18n
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
          `language_id` AS `languageId`
        FROM `movies_titles`
        WHERE `movie_id` = ?
          ORDER BY `title` ASC",
        "d",
        [ $this->id ]
      );
      $c = count($this->titles);
      for ($i = 0; $i < $c; ++$i) {
        settype($this->titles[$i]["isDisplayTitle"], "boolean");
      }
    }
    return $this->titles;
  }

}
