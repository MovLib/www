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
namespace MovLib\Data;

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
final class RevisionEntitySet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "revisions";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "revision";

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {

  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractSet $set, $in) {

  }


  /**
   * Load revisions.
   *
   * @param string $entityClassName
   *   The entity's class name without namespace.
   * @param integer $entityId
   *   The entity's identifier.
   * @param integer $offset
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @return this
   */
  public function loadRevisions($entityClassName, $entityId, $offset, $limit) {
    $entityClassName = "\\MovLib\\Data\\{$entityClassName}\\{$entityClassName}Revision";
    $entityTypeId    = constant("{$entityClassName}::ENTITY_ID");
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `revisions`.`id` AS `id`,
  `revisions`.`user_id` AS `userId`,
  `revisions`.`id` + 0 AS `revision`
FROM `revisions`
WHERE `revisions`.`revision_entity_id` = {$entityTypeId} AND `revisions`.`entity_id` = {$entityId}
ORDER BY `id` DESC
LIMIT {$limit}
OFFSET {$offset}
SQL
    );
    $userIds = [];
    while ($entity = $result->fetch_object($entityClassName, [ $this->diContainer, null, false ])) {
      $this->entities[$entity->id] = $entity;
      $userIds[] = $entity->userId;
    }
    $result->free();
    if (!empty($userIds)) {
      $userSet = (new UserSet($this->diContainer))->loadIdentifiers($userIds);
      foreach ($this->entities as $key => $value) {
        $this->entities[$key]->user = $userSet->entities[$value->userId];
      }
    }
    return $this;
  }

}
