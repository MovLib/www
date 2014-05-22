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
namespace MovLib\Data\Revision;

use \MovLib\Data\User\UserSet;

/**
 * Defines the revision set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class RevisionSet extends \MovLib\Core\AbstractDatabase implements \Iterator {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  protected $entityId;

  public $revisions = [];

  protected $revisionEntityClassName;

  protected $revisionEntityTypeId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __construct(\MovLib\Data\Revision\RevisionInterface $entity) {
    $this->entityId                = $entity->id;
    $this->revisionEntityClassName = get_class($entity) . "Revision";
    $this->revisionEntityTypeId    = constant("{$this->revisionEntityClassName}::ENTITY_ID");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getTotalCount() {
    return (integer) $this->getMySQLi()->query(
      "SELECT COUNT(*) FROM `revisions` WHERE `revision_entity_id` = {$this->revisionEntityTypeId} AND `entity_id` = {$this->entityId} LIMIT 1"
    )->fetch_all()[0][0];
  }

  public function load($offset, $limit, \MovLib\Core\DIContainer $diContainer) {
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `id` + 0 AS `id`,
  `user_id` AS `userId`
FROM `revisions`
WHERE `revision_entity_id` = {$this->revisionEntityTypeId}
  AND `entity_id` = {$this->entityId}
ORDER BY `id` DESC
LIMIT {$limit} OFFSET {$offset}
SQL
    );

    $userIds = null;
    /* @var $revisionEntity \MovLib\Data\Revision\AbstractEntity */
    while ($revisionEntity = $result->fetch_object($this->revisionEntityClassName)) {
      $this->revisions[$revisionEntity->id] = $revisionEntity;
      $userIds[] = $revisionEntity->userId;
    }
    $result->free();

    if ($userIds) {
      $userSet = (new UserSet($diContainer))->loadIdentifiers($userIds);
      foreach ($this->revisions as $key => $value) {
        $this->revisions[$key]->user = $userSet->entities[$value->userId];
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Iterator Methods


  /**
   * Get the current entity.
   *
   * @return \MovLib\Data\Revision\AbstractEntity|boolean
   *   The current entity, or <code>FALSE</code> if there the array is empty or the current entity is invalid.
   */
  public function current() {
    return current($this->revisions);
  }

  /**
   * Get the unique identifier of the current entity.
   *
   * @return mixed
   *   The unique identifier of the current entity, <code>NULL</code> if there are no entities or the current pointer is
   *   beyond the array's elements.
   */
  public function key() {
    return key($this->revisions);
  }

  /**
   * Get the next entity.
   *
   * @return \MovLib\Data\Revision\AbstractEntity|boolean
   *   The next entity, or <code>FALSE</code> if there is no next entity.
   */
  public function next() {
    return next($this->revisions);
  }

  /**
   * Rewind the iterator to the first entity.
   *
   * @return \MovLib\Data\Revision\AbstractEntity|boolean
   *   The first entity, or <code>FALSE</code> if there are no more entities.
   */
  public function rewind() {
    return reset($this->revisions);
  }

  /**
   * Check if the current position is valid.
   *
   * @return boolean
   *   <code>TRUE</code> if the current position is valid, <code>FALSE</code> otherwise.
   */
  public function valid() {
    return key($this->revisions) !== null;
  }

}
