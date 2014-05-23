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
final class GenreRevision extends \MovLib\Data\Revision\AbstractRevisionEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all the genre's localized names, keyed by language code.
   *
   * @var array
   */
  public $names;

  /**
   * Associative array containing all the genre's localized descriptions, keyed by language code.
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
   * @param integer $genreId [optional]
   *   The genre's unique identifier, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no genre exists for the given genre identifier.
   */
  public function __construct($genreId = null) {
    if ($genreId) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
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
    AND `revisions`.`revision_entity_id` = {$this->revisionEntityId}
WHERE `genres`.`id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $genreId);
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

      if (!$found) {
        throw new NotFoundException("Couldn't find Genre {$genreId}");
      }

      $this->jsonDecode($this->descriptions, $this->names, $this->wikipediaLinks);
      parent::__construct();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return array_merge(parent::__sleep(), [ "descriptions", "names", "wikipediaLinks" ]);
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
      "names",
      $this->names,
      $oldRevision->names,
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
    $this->getMySQLi()->query("UPDATE `genres` SET {$deleted}{$dynCols} WHERE `id` = {$this->entityId}");
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function initialCommit($languageCode) {
    $this->getMySQLi()->query("INSERT INTO `genres` SET {$this->getDynamicColumnUpdateQuery(
      $languageCode,
      "descriptions",
      $this->descriptions,
      "names",
      $this->names,
      "wikipedia",
      $this->wikipediaLinks
    )}");
    return $this;
  }

}
