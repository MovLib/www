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

use \MovLib\Data\Date;
use \MovLib\Data\Route\EntityRoute;
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
final class Movie extends \MovLib\Data\Image\AbstractReadOnlyImageEntity {
  use \MovLib\Data\Movie\MovieTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's rating.
   *
   * @var null|float
   */
  public $bayesRating;

  /**
   * The movie's countries.
   *
   * @var null|array
   */
  public $countries;

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
   * @var null|integer
   */
  public $rank;

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
   * @var null|integer
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
      $mysqli = $this->getMySQLi();
      $stmt   = $mysqli->prepare(<<<SQL
SELECT
  `movies`.`id`,
  `movies`.`year`,
  `movies`.`rank`,
  `movies`.`votes`,
  `movies`.`rating`,
  `movies`.`runtime`,
  `movies`.`deleted`,
  `movies`.`changed`,
  `movies`.`created`,
  `movies`.`mean_rating`,
  `movies_taglines`.`tagline`,
  `original_title`.`title`,
  `original_title`.`language_code`,
  IFNULL(`display_title`.`title`, `original_title`.`title`),
  COLUMN_GET(`movies`.`dyn_synopses`, '{$this->intl->languageCode}' AS CHAR),
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`),
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
        $this->displayTitleLanguageCode,
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
        $this->genres[] = $genre;
      }
      $result->free();
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
    if ($this->year) {
      $this->displayTitleAndYear = $this->intl->t("{0} ({1})", [ $this->displayTitle, $this->year ]);
      $this->year = new Date($this->year);
    }
    else {
      $this->displayTitleAndYear = $this->displayTitle;
    }
    $this->imageAlternativeText = $this->intl->t("{movie_title} poster.", [ "movie_title" => $this->displayTitleAndYear ]);
    $this->imageDirectory       = "upload://movie/{$this->id}/poster";
    $this->route                = new EntityRoute($this->intl, "/movie/{0}", $this->id, "/movies");
    return parent::init();
  }

}
