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

use \MovLib\Core\Database\Database;
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
final class JobRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "JobRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 10;


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

  /**
   * {@inheritdoc}
   */
  protected $tableName = "jobs";

  /**
   * Associative array containing all the genre's localized wikipedia links, keyed by language code.
   *
   * @var array
   */
  public $wikipediaLinks;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new job revision.
   *
   * @param integer $id
   *   The job's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no job was found for the given unique identifier.
   */
  public function __construct($id = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
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
    AND `revisions`.`revision_entity_id` = 10
WHERE `jobs`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
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
        throw new NotFoundException("Couldn't find Job {$id}");
      }
    }

    if ($this->id) {
      $connection->dynamicDecode($this->descriptions);
      $connection->dynamicDecode($this->titlesSex0);
      $connection->dynamicDecode($this->titlesSex1);
      $connection->dynamicDecode($this->titlesSex2);
      $connection->dynamicDecode($this->wikipediaLinks);
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [
        "descriptions",
        "titlesSex0",
        "titlesSex1",
        "titlesSex2",
        "wikipediaLinks",
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
      ->setDynamicConditional("descriptions", $languageCode, $this->descriptions, $oldRevision->descriptions)
      ->setDynamicConditional("titles_sex0", $languageCode, $this->titlesSex0, $oldRevision->titlesSex0)
      ->setDynamicConditional("titles_sex1", $languageCode, $this->titlesSex1, $oldRevision->titlesSex1)
      ->setDynamicConditional("titles_sex2", $languageCode, $this->titlesSex2, $oldRevision->titlesSex2)
      ->setDynamicConditional("wikipedia", $languageCode, $this->wikipediaLinks, $oldRevision->wikipediaLinks)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->set("descriptions", $this->descriptions)
      ->set("titles_sex0", $this->titlesSex0)
      ->set("titles_sex1", $this->titlesSex1)
      ->set("titles_sex2", $this->titlesSex2)
      ->set("wikipedia", $this->wikipediaLinks)
    ;
  }

}
