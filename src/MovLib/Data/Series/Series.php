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

use \MovLib\Data\Date;
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
class Series extends \MovLib\Data\AbstractEntity implements \MovLib\Data\RatingInterface {
  use \MovLib\Data\RatingTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


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
   * The series's award count.
   *
   * @var integer
   */
  public $awardCount;

  /**
   *  The series's display title in the current locale.
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
   * The year the series was cancelled.
   *
   * @var integer
   */
  public $endYear;

  /**
   * The series's original title.
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
   * The series's release count.
   *
   * @var null|integer
   */
  public $releaseCount;

  /**
   * The series's season count.
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
   * The series's status.
   *
   * One of the STATUS_ constants.
   *
   * @var integer
   */
  public $status;

  /**
   * The series's synopsis in the current locale.
   *
   * @var null|string
   */
  public $synopsis;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new series object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The series's unique identifier to instantiate, defaults to <code>NULL</code> (no series will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `series`.`id` AS `id`,
  `series`.`changed` AS `changed`,
  `series`.`created` AS `created`,
  `series`.`deleted` AS `deleted`,
  `series`.`end_year` AS `endYear`,
  `series`.`mean_rating` AS `ratingMean`,
  `series`.`rank` AS `ratingRank`,
  `series`.`rating` AS `ratingBayes`,
  `series`.`start_year` AS `startYear`,
  `series`.`status` AS `status`,
  COLUMN_GET(`series`.`dyn_synopses`, '{$this->intl->languageCode}' AS CHAR) AS `synopsis`,
  COLUMN_GET(`series`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  `original_title`.`title`,
  `original_title`.`language_code`,
  IFNULL(`display_title`.`title`, `original_title`.`title`),
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`),
  `series`.`votes` AS `ratingVotes`,
  `series`.`count_awards` AS `awardCount`,
  `series`.`count_seasons` AS `seasonCount`,
  `series`.`count_releases` AS `releaseCount`
FROM `series`
  LEFT JOIN `series_display_titles`
    ON `series_display_titles`.`series_id` = `series`.`id`
    AND `series_display_titles`.`language_code` = '{$this->intl->languageCode}'
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
        $this->originalTitleLanguageCode,
        $this->displayTitle,
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
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the series.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `series` SET
  `dyn_synopses`  = COLUMN_ADD(`dyn_synopses`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia` = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `end_year`      = ?,
  `start_year`    = ?,
  `status`        = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "ssddi",
      $this->synopsis,
      $this->wikipedia,
      $this->endYear->year,
      $this->startYear->year,
      $this->status
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->startYear     && ($this->startYear = new Date($this->startYear));
    $this->endYear       && ($this->endYear = new Date($this->endYear));
    $this->pluralKey   = "series";
    $this->singularKey = "series";
    return parent::init();
  }

}
