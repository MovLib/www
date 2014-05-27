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
namespace MovLib\Data\SystemPage;

use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for system page entities.
 *
 * @property \MovLib\Data\SystemPage\SystemPage $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SystemPageRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "SystemPageRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 13;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all the system page's localized titles, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $titles;

  /**
   * Associative array containing all the system page's localized texts, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $texts;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 13;

  /**
   * {@inheritdoc}
   */
  protected $tableName = "system_pages";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new system page revision.
   *
   * @param integer $id
   *   The system page's unique identifier to load the revision for.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no system page was found for the given unique identifier.
   */
  public function __construct($id) {
    $connection = Database::getConnection();
    $stmt = $connection->prepare(<<<SQL
SELECT
  `system_pages`.`id`,
  `revisions`.`user_id`,
  `system_pages`.`changed` + 0,
  COLUMN_JSON(`system_pages`.`dyn_texts`),
  COLUMN_JSON(`system_pages`.`dyn_titles`)
FROM `system_pages`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `system_pages`.`id`
    AND `revisions`.`id` = `system_pages`.`changed`
    AND `revisions`.`revision_entity_id` = 13
WHERE `system_pages`.`id` = ?
LIMIT 1
SQL
    );
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $stmt->bind_result(
      $this->entityId,
      $this->userId,
      $this->id,
      $this->texts,
      $this->titles
    );
    $found = $stmt->fetch();
    $stmt->close();
    if ($found === false) {
      throw new NotFoundException("Couldn't find system page for {$id}.");
    }
    if ($this->id) {
      $this->texts  = json_decode($this->texts, true);
      $this->titles = json_decode($this->titles, true);
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [ "texts", "titles" ]);
    }
    return $properties;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function getCommitQuery(\MovLib\Core\Database\Connection $connection) {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  protected function addCommitFields(\MovLib\Core\Database\Query\Update $update) {
    return $update
      ->table("system_pages")
      ->dynamicColumn("texts", $this->texts)
      ->dynamicField("titles", $this->titles)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Query\Insert $insert) {
    return $insert
      ->table("system_pages")
      ->dynamicColumn("texts", $this->texts)
      ->dynamicField("titles", $this->titles)
    ;
  }

}
