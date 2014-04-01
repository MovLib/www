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

use \MovLib\Data\Genre\GenreSet;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the movie object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Movie extends \MovLib\Data\AbstractDatabaseEntity {
  use \MovLib\Data\Movie\MovieTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's countries.
   *
   * @var null|array
   */
  public $countries;

  /**
   * The movie's display title for the current locale.
   *
   * @var string
   */
  public $displayTitle;

  /**
   * The display title's ISO 639-1 language code.
   *
   * @var string
   */
  public $displayTitleLanguageCode;

  /**
   * The movie's genres.
   *
   * @var array
   */
  public $genres;

  /**
   * The movie's mean rating.
   *
   * @var float
   */
  public $meanRating;

  /**
   * The movie's original title.
   *
   * @var string
   */
  public $originalTitle;

  /**
   * The original title's ISO 639-1 language code.
   *
   * @var string
   */
  public $originalTitleLanguageCode;

  /**
   * The movie's global rank.
   *
   * @var integer
   */
  public $rank;

  /**
   * The movie's rating.
   *
   * @var float
   */
  public $rating;

  /**
   * The movie's runtime in seconds.
   *
   * @var integer
   */
  public $runtime;

  /**
   * The movie's synopsis in the current locale.
   *
   * @var string
   */
  public $synopsis;

  /**
   * The movie's tagline.
   *
   * @var string
   */
  public $tagline;

  /**
   * The movie's tagline ISO 639-1 language code.
   *
   * @var string
   */
  public $taglineLanguageCode;

  /**
   * The movie's votes.
   *
   * @var integer
   */
  public $votes;

  /**
   * The movie's year.
   *
   * @var \MovLib\Data\Date
   */
  public $year;


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
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `movies`.`id` AS `id`,
  `movies`.`year` AS `year`,
  `movies`.`rank` AS `rank`,
  `movies`.`votes` AS `votes`,
  `movies`.`rating` AS `rating`,
  `movies`.`runtime` AS `runtime`,
  `movies`.`deleted` AS `deleted`,
  `movies`.`changed` AS `changed`,
  `movies`.`created` AS `created`,
  `movies`.`mean_rating` AS `meanRating`,
  `movies_taglines`.`tagline` AS `tagline`,
  `original_title`.`title` AS `originalTitle`,
  `original_title`.`language_code` AS `originalTitleLanguageCode`,
  IFNULL(`display_title`.`title`, `original_title`.`title`) AS `displayTitle`,
  COLUMN_GET(`movies`.`dyn_synopses`, '{$this->intl->languageCode}' AS BINARY) AS `synopsis`,
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`) AS `displayTitleLanguageCode`
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
WHERE `movies`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->year,
        $this->rank,
        $this->votes,
        $this->rating,
        $this->runtime,
        $this->deleted,
        $this->changed,
        $this->created,
        $this->meanRating,
        $this->tagline,
        $this->originalTitle,
        $this->originalTitleLanguageCode,
        $this->displayTitle,
        $this->synopsis,
        $this->displayTitleLanguageCode
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Movie {$id}");
      }
      $result   = $this->getMySQLi()->query("SELECT `genre_id` FROM `movies_genres` WHERE `movie_id` = {$this->id}");
      $genreIds = $result->fetch_all();
      $result->free();
      if (!empty($genreIds)) {
        $this->genres = (new GenreSet($this->diContainer))->getIdentifiers(
          array_column($genreIds, 0),
          "`name` {$this->collations[$this->intl->languageCode]} DESC"
        );
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->toDates([ &$this->year ]);
    return parent::init();
  }

}
