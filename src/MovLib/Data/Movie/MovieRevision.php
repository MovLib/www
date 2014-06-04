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
use \MovLib\Data\Title;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for movies.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class MovieRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "MovieRevision";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@inheritdoc}
   */
  public static $originatorClassId = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's awards.
   *
   * @var array
   */
  public $awards;

  /**
   * The movie's country codes.
   *
   * @var array
   */
  public $countries;

  /**
   * The movie's crew.
   *
   * @var array
   */
  public $crew;

  /**
   * The movie's genre identifiers.
   *
   * @var array
   */
  public $genres;

  /**
   * The movie's language codes.
   *
   * @var array
   */
  public $languages;

  /**
   * The movie's localized links.
   *
   * @var array
   */
  public $links;

  /**
   * The movie's release identifiers.
   *
   * @var array
   */
  public $releases;

  /**
   * The movie's runtime in seconds.
   *
   * @var array
   */
  public $runtime;

  /**
   * The movie's localized synopses.
   *
   * @var array
   */
  public $synopses;

  /**
   * The movie's taglines.
   *
   * @var array
   */
  public $taglines;

  /**
   * The movie's titles.
   *
   * @var array
   */
  public $titles;

  /**
   * The movie's trailers.
   *
   * @var array
   */
  public $trailers;

  /**
   * The movie's localized wikipedia links.
   *
   * @var array
   */
  public $wikipediaLinks;

  /**
   * The movie's year.
   *
   * @var array
   */
  public $year;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new series revision.
   *
   * @param integer $id
   *   The movie's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no movie was found for the given unique identifier.
   */
  public function __construct($id = null) {
    $connection = Database::getConnection();
    if ($id) {
      $originalTitleId = null;
      $stmt = $connection->prepare(<<<SQL
SELECT
  `movies`.`id`,
  `revisions`.`user_id`,
  `movies`.`changed` + 0,
  `movies`.`deleted`,
  COLUMN_JSON(`movies`.`dyn_synopses`),
  COLUMN_JSON(`movies`.`dyn_wikipedia`),
  `movies`.`runtime`,
  `movies`.`year`,
  `movies_original_titles`.`title_id`
FROM `movies`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `movies`.`id`
    AND `revisions`.`id` = `movies`.`changed`
    AND `revisions`.`revision_entity_id` = {$this::$originatorClassId}
  INNER JOIN `movies_original_titles`
    ON `movies_original_titles`.`movie_id` = `movies`.`id`
WHERE `movies`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->originatorId,
        $this->userId,
        $this->id,
        $this->deleted,
        $this->synopses,
        $this->wikipediaLinks,
        $this->runtime,
        $this->year,
        $originalTitleId
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find movie for {$id}.");
      }
      $originalTitleId = (integer) $originalTitleId;

      // Retrieve the genre identifiers.
      $this->genres = $connection->query("SELECT `genre_id` FROM `movies_genres` WHERE `movie_id` = {$id} ORDER BY `genre_id` ASC")->fetch_all();
      $this->genres = array_column($this->genres, 0);

      // Retrieve the titles and display title language codes.
      $result = $connection->query(<<<SQL
SELECT
  `movies_titles`.`id`,
  COLUMN_JSON(`movies_titles`.`dyn_comments`) AS `comments`,
  `movies_titles`.`language_code` AS `languageCode`,
  `movies_titles`.`title`,
  `movies_display_titles`.`language_code` AS `displayLanguageCode`
FROM `movies_titles`
  LEFT JOIN `movies_display_titles`
    ON `movies_display_titles`.`title_id` = `movies_titles`.`id`
WHERE `movies_titles`.`movie_id` = {$id}
ORDER BY `movies_titles`.`id` ASC
SQL
      );
      while ($row = $result->fetch_object()) {
        $row->id = (integer) $row->id;
        if (empty($this->titles[$row->id])) {
          $connection->dynamicDecode($row->comments);
          $this->titles[$row->id] = new Title();
          $this->titles[$row->id]->id = $row->id;
          $this->titles[$row->id]->comments = $row->comments;
          $this->titles[$row->id]->languageCode = $row->languageCode;
          if ($row->id === $originalTitleId) {
            $this->titles[$row->id]->original = true;
          }
          $this->titles[$row->id]->title = $row->title;
        }
        if (isset($row->displayLanguageCode)) {
          // Avoid duplicates.
          $this->titles[$row->id]->displayLanguageCodes[$row->displayLanguageCode] = true;
          $this->titles[$row->id]->display = true;
        }
      }
      $result->free();

      // Retrieve the taglines and display tagline language codes.
      $result = $connection->query(<<<SQL
SELECT
  `movies_taglines`.`id`,
  COLUMN_JSON(`movies_taglines`.`dyn_comments`) AS `comments`,
  `movies_taglines`.`language_code` AS `languageCode`,
  `movies_taglines`.`tagline`,
  `movies_display_taglines`.`language_code` AS `displayLanguageCode`
FROM `movies_taglines`
  LEFT JOIN `movies_display_taglines`
    ON `movies_display_taglines`.`tagline_id` = `movies_taglines`.`id`
WHERE `movies_taglines`.`movie_id` = {$id}
ORDER BY `movies_taglines`.`id` ASC
SQL
      );
      while ($row = $result->fetch_object()) {
        $row->id = (integer) $row->id;
        if (empty($this->taglines[$row->id])) {
          $connection->dynamicDecode($row->comments);
          $this->taglines[$row->id] = (object) [
            "id" => $row->id,
            "comments" => $row->comments,
            "languageCode" => $row->languageCode,
            "tagline" => $row->tagline,
            "displayLanguageCodes" => [],
          ];
        }
        if (isset($row->displayLanguageCode)) {
          // Avoid duplicates.
          $this->taglines[$row->id]->displayLanguageCodes[$row->displayLanguageCode] = true;
        }
      }
      $result->free();

      // Retrieve the crew.
      $result = $connection->query(<<<SQL
SELECT
  `id`,
  `job_id` AS `jobId`,
  COLUMN_JSON(`dyn_role`) AS `roles`,
  `alias_id` AS `aliasId`,
  `company_id` AS `companyId`,
  `person_id` AS `personId`,
  `role_id` AS `roleId`
FROM `movies_crew`
WHERE `movie_id` = {$id}
ORDER BY `id` ASC
SQL
      );
      while ($row = $result->fetch_object()) {
        $row->id              = (integer) $row->id;
        $row->aliasId         = (integer) $row->aliasId;
        $row->companyId       = (integer) $row->companyId;
        $row->personId        = (integer) $row->personId;
        $row->roleId          = (integer) $row->roleId;
        $connection->dynamicDecode($row->roles);
        $this->crew[$row->id] = $row;
      }
      $result->free();

      // Retrieve the awards.
      $result = $connection->query(<<<SQL
SELECT
  `id`,
  `award_category_id` AS `awardCategoryId`,
  `award_id` AS `awardId`,
  `event_id` AS `eventId`,
  `company_id` AS `companyId`,
  `person_id` AS `personId`,
  `won`
FROM `movies_awards`
WHERE `movie_id` = {$id}
ORDER BY `id` ASC
SQL
      );
      while ($row = $result->fetch_object()) {
        $row->id                = (integer) $row->id;
        $row->awardCategoryId   = (integer) $row->awardCategoryId;
        $row->awardId           = (integer) $row->awardId;
        $row->eventId           = (integer) $row->eventId;
        $row->companyId         = (integer) $row->companyId;
        $row->personId          = (integer) $row->personId;
        $row->won               = (boolean) $row->won;
        $this->awards[$row->id] = $row;
      }
      $result->free();

      // @todo Add all other cross references or implement their own history.

    }
    if ($this->id) {
      $connection->dynamicDecode($this->synopses);
      $connection->dynamicDecode($this->wikipediaLinks);
      $this->runtime && ($this->runtime = (integer) $this->runtime);
      $this->year    && ($this->year    = (integer) $this->year);
    }
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [
        "awards",
        "countries",
        "crew",
        "genres",
        "languages",
        "links",
        "releases",
        "runtime",
        "synopses",
        "taglines",
        "titles",
        "trailers",
        "wikipediaLinks",
        "year",
      ]);
    }
    return $properties;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function addCommitFields(\MovLib\Core\Database\Query\Update $update, \MovLib\Core\Revision\RevisionInterface $oldRevision, $languageCode) {
    return $update
      ->set("runtime", $this->runtime)
      ->setDynamicConditional("synopses", $languageCode, $this->synopses, $oldRevision->synopses)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
      ->set("year", $this->year)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->set("runtime", $this->runtime)
      ->setDynamic("synopses", $this->synopses)
      ->setDynamic("wikipedia", $this->wikipediaLinks)
      ->set("year", $this->year)
    ;
  }

}
