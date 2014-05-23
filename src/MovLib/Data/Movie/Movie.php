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
namespace MovLib\Data\Movie;

use \MovLib\Component\Date;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the movie object.
 *
 * @property-read array|null $countries The movie's countries.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Movie extends \MovLib\Data\Image\AbstractReadOnlyImageEntity implements \MovLib\Data\RatingInterface {
  use \MovLib\Data\RatingTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's countries.
   *
   * @var null|array
   */
  private $countries;

  /**
   * The movie's total award count.
   *
   * @var integer
   */
  public $countAwards;

  /**
   * The movie's total release count.
   *
   * @var integer
   */
  public $countReleases;

  /**
   * The movie's display tagline <b>for</b> the current locale.
   *
   * @var string
   */
  public $displayTagline;

  /**
   * The movie's display tagline's unique identifier.
   *
   * @var integer
   */
  public $displayTaglineId;

  /**
   * The movie's display tagline's ISO 639-1 language code.
   *
   * @var string
   */
  public $displayTaglineLanguageCode;

  /**
   * The movie's display title <b>for</b> the current locale.
   *
   * <b>NOTE</b><br>
   * The title itself may have a totally different locale than the current display locale, that's why it says <i>for</i>
   * in the short comment and not <i>in</i>. Always set the <code>"lang"</code> attribute if you display the title
   * anywhere if the locale of the title differs from the current display locale.
   *
   * @var string
   */
  public $displayTitle;

  /**
   * The movie's display title with the year appended in parantheses <b>for</b> the current locale.
   *
   * <b>NOTE</b><br>
   * The value is equals to the display title if the year is unknown.
   *
   * <b>NOTE</b><br>
   * The title itself may have a totally different locale than the current display locale, that's why it says <i>for</i>
   * in the short comment and not <i>in</i>. Always set the <code>"lang"</code> attribute if you display the title
   * anywhere if the locale of the title differs from the current display locale.
   *
   * @var string
   */
  public $displayTitleAndYear;

  /**
   * The movie's display title's unique identifier.
   *
   * @var integer
   */
  public $displayTitleId;

  /**
   * The display title's ISO 639-1 language code.
   *
   * @var string
   */
  public $displayTitleLanguageCode;

  /**
   * The movie's genres.
   *
   * @var \MovLib\Data\Genre\GenreSet
   */
  public $genreSet;

  /**
   * The movie's original title.
   *
   * @var string
   */
  public $originalTitle;

  /**
   * The movie's original title's unique identifier.
   *
   * @var integer
   */
  public $originalTitleId;

  /**
   * The original title's ISO 639-1 language code.
   *
   * @var string
   */
  public $originalTitleLanguageCode;

  /**
   * The movie's runtime in seconds.
   *
   * @var null|integer
   */
  public $runtime;

  /**
   * The movie's synopsis in the current locale.
   *
   * @var null|string
   */
  public $synopsis;

  /**
   * The movie's taglines.
   *
   * @see Movie::getTaglines()
   * @var mixed
   */
  protected $taglines;

  /**
   * The movie's titles.
   *
   * @see Movie::getTitles()
   * @var mixed
   */
  protected $titles;

  /**
   * The movie's year.
   *
   * @var integer
   */
  public $year;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "movies";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "movie";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The movie's unique identifier to instantiate, defaults to <code>NULL</code> (no movie will be loaded).
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $mysqli = $this->getMySQLi();
      $stmt   = $mysqli->prepare(<<<SQL
SELECT
  `movies`.`id`,
  `movies`.`count_awards`,
  `movies`.`count_releases`,
  `movies`.`year`,
  `movies`.`rank`,
  `movies`.`votes`,
  `movies`.`rating`,
  `movies`.`runtime`,
  `movies`.`deleted`,
  `movies`.`changed`,
  `movies`.`created`,
  COLUMN_GET(`movies`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `movies`.`mean_rating`,
  `movies_taglines`.`tagline`,
  `movies_taglines`.`id` AS `taglineId`,
  `movies_taglines`.`language_code`,
  `original_title`.`title`,
  `original_title`.`id` AS `originalTitleId`,
  `original_title`.`language_code`,
  IFNULL(`display_title`.`title`, `original_title`.`title`),
  IFNULL(`display_title`.`id`, `original_title`.`id`) AS `displayTitleId`,
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`),
  COLUMN_GET(`movies`.`dyn_synopses`, '{$this->intl->languageCode}' AS CHAR),
  `posters`.`id`,
  HEX(`posters`.`cache_buster`),
  `posters`.`extension`,
  `posters`.`styles`
FROM `movies`
  LEFT JOIN `movies_display_titles`
    ON `movies_display_titles`.`movie_id` = `movies`.`id`
    AND `movies_display_titles`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `movies_titles` AS `display_title`
    ON `display_title`.`id` = `movies_display_titles`.`title_id`
  LEFT JOIN `movies_original_titles`
    ON `movies_original_titles`.`movie_id` = `movies`.`id`
  LEFT JOIN `movies_titles` AS `original_title`
    ON `original_title`.`id` = `movies_original_titles`.`title_id`
  LEFT JOIN `movies_display_taglines`
    ON `movies_display_taglines`.`movie_id` = `movies`.`id`
    AND `movies_display_taglines`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `movies_taglines`
    ON `movies_taglines`.`id` = `movies_display_taglines`.`tagline_id`
  LEFT JOIN `display_posters`
    ON `display_posters`.`movie_id` = `movies`.`id`
    AND `display_posters`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `posters`
    ON `posters`.`id` = `display_posters`.`poster_id`
    AND `posters`.`deleted` = false
WHERE `movies`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->countAwards,
        $this->countReleases,
        $this->year,
        $this->ratingRank,
        $this->ratingVotes,
        $this->ratingBayes,
        $this->runtime,
        $this->deleted,
        $this->changed,
        $this->created,
        $this->wikipedia,
        $this->ratingMean,
        $this->displayTagline,
        $this->displayTaglineId,
        $this->displayTaglineLanguageCode,
        $this->originalTitle,
        $this->originalTitleId,
        $this->originalTitleLanguageCode,
        $this->displayTitle,
        $this->displayTitleId,
        $this->displayTitleLanguageCode,
        $this->synopsis,
        $this->imageFilename,
        $this->imageCacheBuster,
        $this->imageExtension,
        $this->imageStyles
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Movie {$id}");
      }

      $result = $mysqli->query(<<<SQL
SELECT
  `genres`.`id`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`
FROM `movies_genres`
  INNER JOIN `genres` ON `genres`.`id` = `movies_genres`.`genre_id`
WHERE `movies_genres`.`movie_id` = {$this->id}
ORDER BY `name` {$this->collations[$this->intl->languageCode]} DESC
SQL
      );
      while ($genre = $result->fetch_object("\\MovLib\\Data\\Genre\Genre", [ $this->diContainer ])) {
        $this->genreSet[] = $genre;
      }
      $result->free();
    }
    if ($this->id) {
      $this->init();
    }
  }

  /**
   * @link http://php.net/language.oop5.overloading#object.get
   */
  public function __get($name) {
    if (isset($this->$name)) {
      return $this->$name;
    }
    return $this->{"get{$name}"}();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the movie.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `movies` SET
  `dyn_synopses`  = COLUMN_ADD(`dyn_synopses`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia` = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `runtime`       = ?,
  `year`          = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "ssdd",
      $this->synopsis,
      $this->wikipedia,
      $this->runtime,
      $this->year->year
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Create new movie.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $mysqli = $this->getMySQLi();
    $mysqli->autocommit(FALSE);

    try {
      $stmtMovie = $mysqli->prepare(<<<SQL
INSERT INTO `movies` (
  `dyn_synopses`,
  `dyn_wikipedia`,
  `runtime`,
  `year`
) VALUES (COLUMN_CREATE('{$this->intl->languageCode}', ?), COLUMN_CREATE('{$this->intl->languageCode}', ?), ?, ?)
SQL
      );
      $stmtMovie->bind_param(
        "ssdd",
        $this->synopsis,
        $this->wikipedia,
        $this->runtime,
        $this->year->year
      );
      $stmtMovie->execute();
      $this->id = $stmtMovie->insert_id;

      $stmtTitle = $mysqli->prepare(
        "INSERT INTO `movies_titles` (`movie_id`, `dyn_comments`, `language_code`, `title`) VALUES (?, '', ?, ?)"
      );
      $stmtTitle->bind_param(
        "dss",
        $this->id,
        $this->originalTitleLanguageCode,
        $this->originalTitle
      );
      $stmtTitle->execute();
      $titleId = $stmtTitle->insert_id;

      $mysqli->query(
        "INSERT INTO `movies_original_titles` (`title_id`, `movie_id`) VALUES ({$titleId}, {$this->id})"
      );
      $mysqli->commit();
    }
    catch (\Exception $e) {
      $mysqli->rollback();
      throw $e;
    }
    finally {
      $mysqli->autocommit(TRUE);
      $mysqli->close();
    }

    return $this->init();
  }

  /**
   * Get the movie's countries.
   *
   * @see Movie::__get()
   * @return array
   *   Array containing all countries of this movie.
   */
  private function getCountries() {
    $countries = $this->intl->getTranslations("countries");
    $result    = $this->getMySQLi()->query("SELECT `country_code` FROM `movies_countries` WHERE `movie_id` = {$this->id}");
    while ($countryCode = $result->fetch_row()[0]) {
      $this->countries[$countryCode] = $countries[$countryCode];
    }
    $result->free();
    return $this->countries;
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = $this->getMySQLi()->prepare("UPDATE `posters` SET `styles` = ? WHERE `id` = ? AND `movie_id` = ?");
    $stmt->bind_param("sdd", $styles, $this->imageFilename, $this->id);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    if ($this->year) {
      $this->displayTitleAndYear = $this->intl->t("{0} ({1})", [ $this->displayTitle, $this->year ]);
    }
    else {
      $this->displayTitleAndYear = $this->displayTitle;
    }
    $this->imageAlternativeText = $this->intl->t("{movie_title} poster.", [ "movie_title" => $this->displayTitleAndYear]);
    $this->imageDirectory       = "upload://movie/{$this->id}/poster";
    return parent::init();
  }

  /**
   * Get the movie's taglines.
   *
   * @return array|boolean
   *   An array containing the movie's taglines where the keys are the unique tagline identifiers and the values the
   *   tagline objects. If this movie has no taglines <code>FALSE</code> is returned.
   */
  public function getTaglines() {
    if ($this->taglines === null) {
      $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `id`,
  COLUMN_GET(`dyn_comments`, '{$this->intl->languageCode}' AS BINARY) AS `comment`,
  `language_code` AS `languageCode`,
  `tagline`
FROM `movies_taglines`
WHERE `movie_id` = {$this->id} AND `id` != {$this->displayTaglineId}
ORDER BY `tagline`{$this->collations[$this->intl->languageCode]}
SQL
      );
      /* @var $tagline \MovLib\Data\Tagline */
      while ($tagline = $result->fetch_object("\\MovLib\\Data\\Tagline")) {
        if ($tagline->tagline == $this->displayTagline) {
          $tagline->display = true;
        }
        $this->taglines[$tagline->id] = $tagline;
      }
      if (empty($this->taglines)) {
        $this->taglines = false;
      }
    }
    return $this->taglines;
  }

  /**
   * Get the movie's titles.
   *
   * @return array|boolean
   *   An array containing the movie's titles where the keys are the unique title identifiers and the values the title
   *   objects. If this movie has no titles <code>FALSE</code> is returned.
   */
  public function getTitles() {
    if ($this->titles === null) {
      $displayTitle = null;
      if ($this->originalTitleId !== $this->displayTitleId) {
        $displayTitle = " AND `id` != {$this->displayTitleId}";
      }
      $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `id`,
  COLUMN_GET(`dyn_comments`, '{$this->intl->languageCode}' AS BINARY) AS `comment`,
  `language_code` AS `languageCode`,
  `title`
FROM `movies_titles`
WHERE `movie_id` = {$this->id} AND `id` != {$this->originalTitleId}{$displayTitle}
ORDER BY `title`{$this->collations[$this->intl->languageCode]}
SQL
      );
      /* @var $title \MovLib\Data\Title */
      while ($title = $result->fetch_object("\\MovLib\\Data\\Title")) {
        if ($title->title == $this->displayTitle) {
          $title->display = true;
        }
        if ($title->title == $this->originalTitle) {
          $title->original = true;
        }
        $this->titles[$title->id] = $title;
      }
      if (empty($this->titles)) {
        $this->titles = false;
      }
    }
    return $this->titles;
  }

}
