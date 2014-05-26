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
final class GenreRevision extends \MovLib\Core\Revision\AbstractRevision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "GenreRevision";
  // @codingStandardsIgnoreEnd

  /**
   * The revision entity's unique identifier.
   *
   * @var integer
   */
  const REVISION_ENTITY_ID = 9;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all the genre's localized names, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $names;

  /**
   * Associative array containing all the genre's localized descriptions, keyed by ISO 639-1 language code.
   *
   * @var array
   */
  public $descriptions;

  /**
   * {@inheritdoc}
   */
  public $revisionEntityId = 9;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genre revision.
   *
   * @param integer $id
   *   The genre's unique identifier to load the revision for. The default value (<code>NULL</code>) is only used for
   *   internal purposes when loaded via <code>fetch_object()</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no genre was found for the given unique identifier.
   */
  public function __construct($id = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `genres`.`id`,
  `revisions`.`user_id`,
  `genres`.`changed` + 0,
  `genres`.`deleted`,
  COLUMN_JSON(`genres`.`dyn_descriptions`),
  COLUMN_JSON(`genres`.`dyn_names`),
  COLUMN_JSON(`genres`.`dyn_wikipedia`)
FROM `genres`
  INNER JOIN `revisions`
    ON `revisions`.`entity_id` = `genres`.`id`
    AND `revisions`.`id` = `genres`.`changed`
    AND `revisions`.`revision_entity_id` = 9
WHERE `genres`.`id` = ?
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
        $this->names,
        $this->wikipediaLinks
      );
      $found = $stmt->fetch();
      $stmt->close();
      if ($found === false) {
        throw new NotFoundException("Couldn't find genre for {$id}.");
      }
    }
    if ($this->id) {
      $this->descriptions = json_decode($this->descriptions, true);
      $this->names        = json_decode($this->names, true);
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    static $properties = null;
    if (!$properties) {
      $properties = array_merge(parent::__sleep(), [ "descriptions", "names", "wikipediaLinks" ]);
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
  protected function addInitialCommitColumns(\MovLib\Core\Database\Insert $insert) {
    return $insert
      ->table("genres")
      ->dynamicColumn("descriptions", $this->descriptions)
      ->dynamicField("names", $this->names)
    ;
  }

  /**
   * {@inheritdoc}
   */
//  public function commit(\MovLib\Core\Database\Connection $connection, $oldRevisionId) {
//    // We have to create an instance of the revision that is currently stored in the database.
//    $old = new static($this->entityId);
//
//    // We have
//    // Check if we should update the deletion status of the entity. Usually that's performed by a deletion request, but
//    // we might be commiting a previous revision that was recreated and the deletion status might have been set to
//    // deleted. In that case we want it to be deleted again.
//    //
//    // @todo Is this really true? Do we want to reset the deletion status just because an old revision was deleted?
//    $deleted = $old->deleted === $this->deleted ? null : "`deleted`=" . (integer) $this->deleted;
//
//    // Check all dynamic columns and build the query parts for those.
//    $dynCols = $connection->dynColBuildUpdate($languageCode, [
//      "descriptions" => [ $this->descriptions, $old->descriptions ],
//      "names"        => [ $this->names, $old->names ],
//      "wikipedia"    => [ $this->wikipediaLinks, $old->wikipediaLinks ],
//    ]);
//
//    // If we have both values, append comma to the deleted part to separate them.
//    if ($deleted && $dynCols) {
//      $deleted .= ",";
//    }
//    // Nothing to commit if both values are NULL.
//    elseif (!$deleted && !$dynCols) {
//      throw new \BadMethodCallException("Nothing to commit.");
//    }
//
//    // Update the genres table with the new data, remember that we are the newest revision and that we have to update
//    // the changed datetime of the genre with our creation datetime.
//    $connection->real_query(
//      "UPDATE `genres` SET `changed` = CAST('{$this->created}' AS DATETIME), {$deleted}{$dynCols} WHERE `id` = {$this->entityId}"
//    );
//
//    return $this;
//  }

}
