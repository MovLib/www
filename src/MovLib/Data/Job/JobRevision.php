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
namespace MovLib\Data\Job;

use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for job entities.
 *
 * @property \MovLib\Data\Job\Job $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class JobRevision extends \MovLib\Data\Revision\AbstractRevisionEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all the job's localized descriptions, keyed by language code.
   *
   * @var array
   */
  public $descriptions;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 10;

  /**
   * Associative array containing all the job's localized unisex titles, keyed by language code.
   *
   * @var array
   */
  public $titlesSex0;

  /**
   * Associative array containing all the job's localized male titles, keyed by language code.
   *
   * @var array
   */
  public $titlesSex1;

  /**
   * Associative array containing all the job's localized female titles, keyed by language code.
   *
   * @var array
   */
  public $titlesSex2;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new job revision.
   *
   * @param integer $jobId [optional]
   *   The job's unique identifier, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no job exists for the given job identifier.
   */
  public function __construct($jobId = null) {
if ($jobId) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `jobs`.`id`,
  `revisions`.`user_id`,
  `jobs`.`changed` + 0,
  `jobs`.`deleted`,
  COLUMN_JSON(`jobs`.`dyn_descriptions`),
  COLUMN_JSON(`jobs`.`dyn_titles_sex0`),
  COLUMN_JSON(`jobs`.`dyn_titles_sex1`),
  COLUMN_JSON(`jobs`.`dyn_titles_sex2`),
  COLUMN_JSON(`jobs`.`dyn_wikipedia`)
FROM `jobs`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `jobs`.`id`
    AND `revisions`.`id` = `jobs`.`changed`
    AND `revisions`.`revision_entity_id` = {$this->revisionEntityId}
WHERE `jobs`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $jobId);
      $stmt->execute();
      $stmt->bind_result(
        $this->entityId,
        $this->userId,
        $this->id,
        $this->deleted,
        $this->descriptions,
        $this->titlesSex0,
        $this->titlesSex1,
        $this->titlesSex2,
        $this->wikipediaLinks
      );
      $found = $stmt->fetch();
      $stmt->close();

      if (!$found) {
        throw new NotFoundException("Couldn't find Job {$jobId}");
      }

      $this->jsonDecode(
        $this->descriptions,
        $this->titlesSex0,
        $this->titlesSex1,
        $this->titlesSex2,
        $this->wikipediaLinks
      );
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return array_merge(parent::__sleep(), [
      "descriptions",
      "titlesSex0",
      "titlesSex1",
      "titlesSex2",
      "wikipediaLinks"
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function commit(\MovLib\Data\Revision\RevisionEntityInterface $oldRevision, $languageCode) {
    // Check if we should update the deletion status of the entity.
    $deleted = $oldRevision->deleted === $this->deleted ? null : "`deleted`=" . (integer) $this->deleted;

    // Check all dynamic columns and build the query parts for those.
    $dynCols = $this->getDynamicColumnUpdateQuery(
      $languageCode,
      "descriptions",
      $this->descriptions,
      $oldRevision->descriptions,
      "titlesSex0",
      $this->titlesSex0,
      $oldRevision->titlesSex0,
      "titlesSex1",
      $this->titlesSex1,
      $oldRevision->titlesSex1,
      "titlesSex2",
      $this->titlesSex2,
      $oldRevision->titlesSex2,
      "wikipedia",
      $this->wikipediaLinks,
      $oldRevision->wikipediaLinks
    );

    // If we have both values, append comma to the deleted part to separate them.
    if ($deleted && $dynCols) {
      $deleted .= ",";
    }
    // If we don't have any of both parts nothing would be updated.
    elseif (!$deleted && !$dynCols) {
      throw new \BadMethodCallException("Nothing to commit.");
    }
    $this->getMySQLi()->query("UPDATE `jobs` SET {$deleted}{$dynCols} WHERE `id` = {$this->entityId}");
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function initialCommit($languageCode) {
    $this->getMySQLi()->query("INSERT INTO `jobs` SET {$this->getDynamicColumnUpdateQuery(
      $languageCode,
      "descriptions",
      $this->descriptions,
      "titlesSex0",
      $this->titlesSex0,
      "titlesSex1",
      $this->titlesSex1,
      "titlesSex2",
      $this->titlesSex2,
      "wikipedia",
      $this->wikipediaLinks
    )}");
    return $this;
  }

}
