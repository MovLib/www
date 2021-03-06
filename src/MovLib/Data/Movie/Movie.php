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

use \MovLib\Core\Database\Database;
use \MovLib\Core\Database\Query\Insert;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
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
final class Movie extends \MovLib\Data\Image\AbstractReadOnlyImageEntity implements \MovLib\Data\Rating\RatingInterface, \MovLib\Core\Revision\OriginatorInterface {
  use OriginatorTrait, RevisionTrait {
    RevisionTrait::postCommit insteadof OriginatorTrait;
    RevisionTrait::postCreate insteadof OriginatorTrait;
  }
  use \MovLib\Data\Rating\RatingTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Movie";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's countries.
   *
   * @see Movie::getCountries()
   * @var null|array
   */
  protected $countries;

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
   * {@inheritdoc}
   */
  public static $tableName = "movies";

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The movie's unique identifier to instantiate, defaults to <code>NULL</code> (no movie will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
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
  COLUMN_GET(`movies`.`dyn_wikipedia`, '{$container->intl->code}' AS CHAR),
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
  COLUMN_GET(`movies`.`dyn_synopses`, '{$container->intl->code}' AS CHAR),
  `posters`.`id`,
  HEX(`posters`.`cache_buster`),
  `posters`.`extension`,
  `posters`.`styles`
FROM `movies`
  LEFT JOIN `movies_display_titles`
    ON `movies_display_titles`.`movie_id` = `movies`.`id`
    AND `movies_display_titles`.`language_code` = '{$container->intl->code}'
  LEFT JOIN `movies_titles` AS `display_title`
    ON `display_title`.`id` = `movies_display_titles`.`title_id`
  LEFT JOIN `movies_original_titles`
    ON `movies_original_titles`.`movie_id` = `movies`.`id`
  LEFT JOIN `movies_titles` AS `original_title`
    ON `original_title`.`id` = `movies_original_titles`.`title_id`
  LEFT JOIN `movies_display_taglines`
    ON `movies_display_taglines`.`movie_id` = `movies`.`id`
    AND `movies_display_taglines`.`language_code` = '{$container->intl->code}'
  LEFT JOIN `movies_taglines`
    ON `movies_taglines`.`id` = `movies_display_taglines`.`tagline_id`
  LEFT JOIN `display_posters`
    ON `display_posters`.`movie_id` = `movies`.`id`
    AND `display_posters`.`language_code` = '{$container->intl->code}'
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

      $result = $connection->query(<<<SQL
SELECT
  `genres`.`id` AS `id`,
  `genres`.`created` AS `created`,
  `genres`.`changed` AS `changed`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$container->intl->code}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$container->intl->defaultCode}' AS CHAR)
  ) AS `name`
FROM `movies_genres`
  INNER JOIN `genres` ON `genres`.`id` = `movies_genres`.`genre_id`
WHERE `movies_genres`.`movie_id` = {$this->id}
ORDER BY `name` {$connection->collate($container->intl->code)} DESC
SQL
      );
      while ($genre = $result->fetch_object("\\MovLib\\Data\\Genre\Genre", [ $container ])) {
        $this->genreSet[] = $genre;
      }
      $result->free();
    }

    parent::__construct($container, $values);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the movie's countries.
   *
   * @return array
   *   Array containing all countries of this movie.
   */
  public function getCountries() {
    if (!$this->countries) {
      $countries = $this->intl->getTranslations("countries");
      $result    = Database::getConnection()->query("SELECT `country_code` FROM `movies_countries` WHERE `movie_id` = {$this->id}");
      while ($row = $result->fetch_row()) {
        $this->countries[] = (object) [ "code" => $row[0], "name" => $countries[$row[0]]->name ];
      }
      $result->free();
    }
    return $this->countries;
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = Database::getConnection()->prepare("UPDATE `posters` SET `styles` = ? WHERE `id` = ? AND `movie_id` = ?");
    $stmt->bind_param("sdd", $styles, $this->imageFilename, $this->id);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);

    if ($this->year) {
      $this->displayTitleAndYear = $this->intl->t("{0} ({1})", [ $this->displayTitle, $this->year ]);
    }
    else {
      $this->displayTitleAndYear = $this->displayTitle;
    }
    $this->imageAlternativeText = $this->intl->t("{movie_title} poster.", [ "movie_title" => $this->displayTitleAndYear]);
    $this->imageDirectory       = "upload://movie/{$this->id}/poster";
    $this->lemma                = $this->displayTitleAndYear;

    return $this;
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
      $displayTaglineCondition = null;
      if ($this->displayTaglineId) {
        $displayTaglineCondition = " AND `id` != {$this->displayTaglineId}";
      }
      $connection = Database::getConnection();
      $result = $connection->query(<<<SQL
SELECT
  `id`,
  COLUMN_GET(`dyn_comments`, '{$this->intl->code}' AS BINARY) AS `comment`,
  `language_code` AS `languageCode`,
  `tagline`
FROM `movies_taglines`
WHERE `movie_id` = {$this->id}{$displayTaglineCondition}
ORDER BY `tagline`{$connection->collate($this->intl->code)}
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
      $connection = Database::getConnection();
      $result = $connection->query(<<<SQL
SELECT
  `id`,
  COLUMN_GET(`dyn_comments`, '{$this->intl->code}' AS BINARY) AS `comment`,
  `language_code` AS `languageCode`,
  `title`
FROM `movies_titles`
WHERE `movie_id` = {$this->id} AND `id` != {$this->originalTitleId}{$displayTitle}
ORDER BY `title`{$connection->collate($this->intl->code)}
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


  // ------------------------------------------------------------------------------------------------------------------- Entity Interface Methods


  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    static $titles = null;

    if (empty($locale) || $locale == $this->intl->locale) {
      return $this->lemma;
    }

    throw new \LogicException("Not implemented!");

    return $this->lemma;
  }


  // ------------------------------------------------------------------------------------------------------------------- Originator Methods


  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Movie\MovieRevision $revision {@inheritdoc}
   * @return \MovLib\Data\Movie\MovieRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->synopses, $this->synopsis);
    $this->setRevisionArrayValue($revision->wikipediaLinks, $this->wikipedia);
    $revision->runtime = $this->runtime;
    $revision->year    = $this->year;

    // Only overwrite the titles if we have them, note that it's impossible to delete all titles, you always have at
    // least the original title. Therefore any check with isset() or empty() would be pointless. The revision has
    // already loaded all existing titles from the database, so no need to do anything if we have no update for them.
    $this->titles && ($revision->titles = $this->titles);

    // @todo Add all other cross references once they can be edited in the interface.

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Movie\MovieRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->runtime   = $revision->runtime;
    $this->synopsis  = $this->getRevisionArrayValue($revision->synopses);
    $this->wikipedia = $this->getRevisionArrayValue($revision->wikipediaLinks);
    $this->year      = $revision->year;
    // @todo Add all other cross references once they can be edited in the interface.
    return $this;
  }

  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search
      ->indexLanguageSuggestion("title", $revision->titles)
      ->indexSimple("year", $this->year)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function preCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision, $oldRevisionId) {
    // @todo Implement saving of cross references once they can be edited.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function postCreate(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision) {
    // Insert original title.
    $this->originalTitleId = (new Insert($connection, "movies_titles"))
      ->set("movie_id", $this->id)
      ->setDynamic("comments", null)
      ->set("language_code", $this->originalTitleLanguageCode)
      ->set("title", $this->originalTitle)
      ->execute()
    ;
    (new Insert($connection, "movies_original_titles"))
      ->set("movie_id", $this->id)
      ->set("title_id", $this->originalTitleId)
      ->execute()
    ;

    // @todo Insert user entered display title, when implemented.
    $this->displayTitleId = $this->originalTitleId;
    (new Insert($connection, "movies_display_titles"))
      ->set("language_code", $this->intl->code)
      ->set("movie_id", $this->id)
      ->set("title_id", $this->originalTitleId)
      ->execute()
    ;
    if ($this->intl->code != $this->intl->defaultCode) {
      (new Insert($connection, "movies_display_titles"))
        ->set("language_code", $this->intl->defaultCode)
        ->set("movie_id", $this->id)
        ->set("title_id", $this->originalTitleId)
        ->execute()
      ;
    }

    return $this;
  }

}
