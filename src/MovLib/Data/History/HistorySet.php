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
namespace MovLib\Data\History;

use \MovLib\Core\Database\Database;
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
final class HistorySet implements \ArrayAccess, \Countable, \Iterator {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity's unique identifier.
   *
   * @var integer
   */
  protected $entityId;

  /**
   * Array containing all loaded revisions.
   *
   * @var array
   */
  protected $revisions = [];

  /**
   * The revision entity's canonical absolute class name this set is working with.
   *
   * @var string
   */
  protected $revisionEntityClassName;

  /**
   * The revision entity's unique identifier this set is working with.
   *
   * @var integer
   */
  protected $revisionEntityTypeId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new revision set.
   *
   * @param string $entityName
   *   The entity's short class name to load revisions for.
   * @param integer $entityId
   *   The entity's unique identifier to load revisions for.
   * @param string $entityNamespace [optional]
   *   The entity's namespace if it differs from <var>"\\MovLib\\Data\\{$entityName}"</var>.
   */
  public function __construct($entityName, $entityId, $entityNamespace = null) {
    if (!isset($entityNamespace)) {
      $entityNamespace = "\\MovLib\\Data\\{$entityName}";
    }
    $this->entityId                = $entityId;
    $this->revisionEntityClassName = "{$entityNamespace}\\{$entityName}Revision";
    $this->revisionEntityTypeId    = constant("{$this->revisionEntityClassName}::REVISION_ENTITY_ID");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getTotalCount() {
    return (integer) Database::getConnection()->query(
      "SELECT COUNT(*) FROM `revisions` WHERE `revision_entity_id` = {$this->revisionEntityTypeId} AND `entity_id` = {$this->entityId} LIMIT 1"
    )->fetch_all()[0][0];
  }

  /**
   * @todo Unify method name to work for pagination, recreate pagination interface?
   * @todo Can we get rid of the dependency injection container dependency? Intl should suffice.
   */
  public function load(\MovLib\Core\Container $container, $offset, $limit) {
    $result = Database::getConnection()->query(<<<SQL
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
    /* @var $revisionEntity \MovLib\Data\Revision\AbstractRevisionEntity */
    while ($revisionEntity = $result->fetch_object($this->revisionEntityClassName)) {
      $this->revisions[$revisionEntity->id] = $revisionEntity;
      $userIds[] = $revisionEntity->userId;
    }
    $result->free();

    if ($userIds) {
      $userSet = (new UserSet($container))->loadIdentifiers($userIds);
      foreach ($this->revisions as $key => $value) {
        $this->revisions[$key]->user = $userSet[$value->userId];
      }
    }

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Iterator Methods


  /**
   * Check if given revision identifier is part of available revisions.
   *
   * @param integer $revisionId
   *   The revision identifier to check.
   * @return boolean
   *   <code>TRUE</code> if it exists, <code>FALSE</code> otherwise.
   */
  public function offsetExists($revisionId) {
    return isset($this->revisions[$revisionId]);
  }

  /**
   * Get revision identified by identifier.
   *
   * @param integer $revisionId
   *   The revision identifier to get.
   * @return \MovLib\Core\Revision\RevisionInterface
   *   The revision identifier by identifier.
   */
  public function offsetGet($revisionId) {
    return $this->revisions[$revisionId];
  }

  /**
   * Set revision in set.
   *
   * @param integer $revisionId [unused]
   *   The revision's unique identifier for this entity.
   * @param \MovLib\Core\Revision\RevisionInterface $revision [unused]
   *   The revision to set.
   * @throws \LogicException
   *   Always throws a logic exception because setting a revision isn't allowed.
   */
  public function offsetSet($revisionId, $revision) {
    throw new \LogicException("You cannot set revisions in a set.");
  }

  /**
   * Unset revision in set.
   *
   * @param integer $revisionId
   *   The revision's unique identifier for this entity to unset.
   * @throws \LogicException
   *   Always throws a logic exception because unsetting a revision isn't allowed.
   */
  public function offsetUnset($revisionId) {
    throw new \LogicException("You cannot unset revisions in a set.");
  }


  // ------------------------------------------------------------------------------------------------------------------- Iterator Methods


  /**
   * Implements <code>count()</code> callback.
   *
   * @return integer
   *   Count of available revisions in this set.
   */
  public function count() {
    return count($this->revisions);
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
