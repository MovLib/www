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
namespace MovLib\Data\Series;

use \MovLib\Component\Date;
use \MovLib\Core\Database\Database;
use \MovLib\Core\Database\Query\Insert;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Represents a single series.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Series extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface, \MovLib\Data\Rating\RatingInterface {
  use \MovLib\Data\Rating\RatingTrait;
  use OriginatorTrait, RevisionTrait {
    RevisionTrait::postCommit insteadof OriginatorTrait;
    RevisionTrait::postCreate insteadof OriginatorTrait;
  }

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Series";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 2;

  /**
   * Unknown status.
   *
   * @var integer
   */
  const STATUS_UNKNOWN = 0;

  /**
   * New status.
   *
   * @var integer
   */
  const STATUS_NEW = 1;

  /**
   * Returning status.
   *
   * @var integer
   */
  const STATUS_RETURNING = 2;

  /**
   * Ended status.
   *
   * @var integer
   */
  const STATUS_ENDED = 3;

  /**
   * Cancelled status.
   *
   * @var integer
   */
  const STATUS_CANCELLED = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The series' award count.
   *
   * @var integer
   */
  public $awardCount;

  /**
   *  The series' display title in the current locale.
   *
   * @var string
   */
  public $displayTitle;

  /**
   * The series' display title's unique identifier.
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
   * The year the series was cancelled.
   *
   * @var integer
   */
  public $endYear;

  /**
   * The series' original title.
   *
   * @var string
   */
  public $originalTitle;

  /**
   * The series' original title's unique identifier.
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
   * The series' release count.
   *
   * @var null|integer
   */
  public $releaseCount;

  /**
   * The series' season count.
   *
   * @var integer
   */
  public $seasonCount;

  /**
   * The year the series was aired for the first time.
   *
   * @var integer
   */
  public $startYear;

  /**
   * The series' status.
   *
   * One of the STATUS_ constants.
   *
   * @var integer
   */
  public $status;

  /**
   * The series' synopsis in the current locale.
   *
   * @var null|string
   */
  public $synopsis;

  /**
   * The series' titles.
   *
   * @see Series::getTitles()
   * @var mixed
   */
  protected $titles;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new series object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The series' unique identifier to instantiate, defaults to <code>NULL</code> (no series will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    if ($id) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `series`.`id` AS `id`,
  `series`.`changed`,
  `series`.`created`,
  `series`.`deleted`,
  `series`.`end_year`,
  `series`.`mean_rating`,
  `series`.`rank`,
  `series`.`rating`,
  `series`.`start_year`,
  `series`.`status`,
  COLUMN_GET(`series`.`dyn_synopses`, '{$container->intl->code}' AS CHAR),
  COLUMN_GET(`series`.`dyn_wikipedia`, '{$container->intl->code}' AS CHAR),
  `original_title`.`title`,
  `original_title`.`id`,
  `original_title`.`language_code`,
  IFNULL(`display_title`.`title`, `original_title`.`title`),
  IFNULL(`display_title`.`id`, `original_title`.`id`),
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`),
  `series`.`votes`,
  `series`.`count_awards`,
  `series`.`count_seasons`,
  `series`.`count_releases`
FROM `series`
  LEFT JOIN `series_display_titles`
    ON `series_display_titles`.`series_id` = `series`.`id`
    AND `series_display_titles`.`language_code` = '{$container->intl->code}'
  LEFT JOIN `series_titles` AS `display_title`
    ON `display_title`.`id` = `series_display_titles`.`title_id`
  LEFT JOIN `series_original_titles`
    ON `series_original_titles`.`series_id` = `series`.`id`
  LEFT JOIN `series_titles` AS `original_title`
    ON `original_title`.`id` = `series_original_titles`.`title_id`
WHERE `series`.`id` = ?
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->endYear,
        $this->ratingMean,
        $this->ratingRank,
        $this->ratingBayes,
        $this->startYear,
        $this->status,
        $this->synopsis,
        $this->wikipedia,
        $this->originalTitle,
        $this->originalTitleId,
        $this->originalTitleLanguageCode,
        $this->displayTitle,
        $this->displayTitleId,
        $this->displayTitleLanguageCode,
        $this->ratingVotes,
        $this->awardCount,
        $this->seasonCount,
        $this->releaseCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Series {$id}");
      }
    }
    parent::__construct($container, $values);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search
      ->indexLanguageSuggestion("title", $revision->titles)
      ->indexSimple("start_year", $revision->startYear)
      ->indexSimple("end_year", $revision->endYear)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $revision->endYear   = $this->endYear->year;
    $revision->startYear = $this->startYear->year;
    $revision->status    = $this->status;
    $this->setRevisionArrayValue($revision->synopses, $this->synopsis);
    $this->setRevisionArrayValue($revision->wikipediaLinks, $this->wikipedia);

    // Only overwrite the titles if we have them, note that it's impossible to delete all titles, you always have at
    // least the original title. Therefore any check with isset() or empty() would be pointless. The revision has
    // already loaded all existing titles from the database, so no need to do anything if we have no update for them.
    $this->titles && ($revision->titles = $this->titles);

    // @todo Add all other cross references once they can be edited in the interface.

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->endYear   = $revision->endYear;
    $this->startYear = $revision->startYear;
    $this->status    = $revision->status;
    $this->synopsis  = $this->getRevisionArrayValue($revision->synopses);
    $this->wikipedia = $this->getRevisionArrayValue($revision->wikipediaLinks);
    // @todo Add all other cross references once they can be edited in the interface.
    return $this;
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
    $this->originalTitleId = (new Insert($connection, "series_titles"))
      ->set("series_id", $this->id)
      ->setDynamic("comments", null)
      ->set("language_code", $this->originalTitleLanguageCode)
      ->set("title", $this->originalTitle)
      ->execute()
    ;
    (new Insert($connection, "series_original_titles"))
      ->set("series_id", $this->id)
      ->set("title_id", $this->originalTitleId)
      ->execute()
    ;

    // @todo Insert user entered display title, when implemented.
    $this->displayTitleId = $this->originalTitleId;
    (new Insert($connection, "series_display_titles"))
      ->set("language_code", $this->intl->code)
      ->set("series_id", $this->id)
      ->set("title_id", $this->originalTitleId)
      ->execute()
    ;
    if ($this->intl->code != $this->intl->defaultCode) {
      (new Insert($connection, "series_display_titles"))
        ->set("language_code", $this->intl->defaultCode)
        ->set("series_id", $this->id)
        ->set("title_id", $this->originalTitleId)
        ->execute()
      ;
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    $this->lemma =& $this->displayTitle;
    if (isset($this->startYear) && !$this->startYear instanceof \stdClass) {
      $this->startYear = new Date($this->startYear);
    }
    if (isset($this->endYear) && !$this->endYear instanceof \stdClass) {
      $this->endYear = new Date($this->endYear);
    }
    return $this;
  }

  /**
   * Get the series' titles.
   *
   * @return array|boolean
   *   An array containing the series' titles where the keys are the unique title identifiers and the values the title
   *   objects. If this series has no titles <code>FALSE</code> is returned.
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
FROM `series_titles`
WHERE `series_id` = {$this->id} AND `id` != {$this->originalTitleId}{$displayTitle}
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

}
