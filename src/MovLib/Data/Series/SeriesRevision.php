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

use \MovLib\Core\Database\Database;
use \MovLib\Data\Title;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision object for series entities.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SeriesRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "SeriesRevision";

  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@inheritdoc}
   */
  public static $originatorClassId = 2;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The series' start year.
   *
   * @var integer
   */
  public $endYear;

  /**
   * The series' start year.
   *
   * @var integer
   */
  public $startYear;

  /**
   * The series' status.
   *
   * @var integer
   */
  public $status;

  /**
   * The series' titles.
   *
   * @var array
   */
  public $titles;

  /**
   * The series' localized synopses.
   *
   * @var array
   */
  public $synopses;

  /**
   * The series' localized wikipedia links.
   *
   * @var array
   */
  public $wikipediaLinks;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new series revision.
   *
   * @param integer $id
   *   The series' unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no series was found for the given unique identifier.
   */
  public function __construct($id = null) {
    $connection = Database::getConnection();
    if ($id) {
      $originalTitleId = null;
      $stmt = $connection->prepare(<<<SQL
SELECT
  `series`.`id`,
  `revisions`.`user_id`,
  `series`.`changed` + 0,
  `series`.`deleted`,
  `series`.`end_year`,
  `series`.`start_year`,
  `series`.`status`,
  COLUMN_JSON(`series`.`dyn_synopses`),
  COLUMN_JSON(`series`.`dyn_wikipedia`),
  `series_original_titles`.`title_id`
FROM `series`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `series`.`id`
    AND `revisions`.`id` = `series`.`changed`
    AND `revisions`.`revision_entity_id` = {$this::$originatorClassId}
  INNER JOIN `series_original_titles`
    ON `series_original_titles`.`series_id` = `series`.`id`
WHERE `series`.`id` = ?
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
        $this->endYear,
        $this->startYear,
        $this->status,
        $this->synopses,
        $this->wikipediaLinks,
        $originalTitleId
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find series for {$id}.");
      }
      $originalTitleId = (integer) $originalTitleId;

      // Retrieve the titles and display title language codes.
      $result = $connection->query(<<<SQL
SELECT
  `series_titles`.`id`,
  COLUMN_JSON(`series_titles`.`dyn_comments`) AS `comments`,
  `series_titles`.`language_code` AS `languageCode`,
  `series_titles`.`title`,
  `series_display_titles`.`language_code` AS `displayLanguageCode`
FROM `series_titles`
  LEFT JOIN `series_display_titles`
    ON `series_display_titles`.`title_id` = `series_titles`.`id`
WHERE `series_titles`.`series_id` = {$id}
ORDER BY `series_titles`.`id` ASC
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

    }
    if ($this->id) {
      $connection->dynamicDecode($this->synopses);
      $connection->dynamicDecode($this->wikipediaLinks);
      $this->endYear   && ($this->endYear   = (integer) $this->endYear);
      $this->startYear && ($this->startYear = (integer) $this->startYear);
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
        "endYear",
        "synopses",
        "wikipediaLinks",
        "startYear",
        "status",
        "titles",
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
      ->setConditional("end_year", $this->endYear, $oldRevision->endYear)
      ->setConditional("start_year", $this->startYear, $oldRevision->startYear)
      ->setConditional("status", $this->status, $oldRevision->status)
      ->setDynamicConditional("synopses", $languageCode, $this->synopses, $oldRevision->synopses)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->set("end_year", $this->endYear)
      ->set("start_year", $this->startYear)
      ->set("status", $this->status)
      ->setDynamic("synopses", $this->synopses)
      ->setDynamic("wikipedia", $this->wikipediaLinks)
    ;
  }
}
