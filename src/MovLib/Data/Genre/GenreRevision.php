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
namespace MovLib\Data\Genre;

use \MovLib\Core\Database\Database;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the revision entity object for genre entities.
 *
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
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

  /**
   * {@inheritdoc}
   */
  protected $tableName = "genres";


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
  protected function addCommitFields(\MovLib\Core\Database\Update $update) {
    return $update
      ->dynamicField("descriptions", $this->descriptions)
      ->dynamicField("names", $this->names)
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCreateFields(\MovLib\Core\Database\Insert $insert) {
    return $insert
      ->dynamicField("descriptions", $this->descriptions)
      ->dynamicField("names", $this->names)
    ;
  }

}
