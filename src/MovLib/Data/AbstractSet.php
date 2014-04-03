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

/**
 * Defines the base class for database set objects.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractSet extends \MovLib\Core\AbstractDatabase implements \MovLib\Data\SetInterface {
  use \MovLib\Data\RouteTrait;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the table names for the <code>FROM</code> part of the {@see AbstractSet::getEntities()} method.
   *
   * @param null|string $where [optional]
   *   The filtering <code>WHERE</code> clause.
   * @param null|string $orderBy [optional]
   *   The filtering <code>ORDER BY</code> clause.
   * @return string
   *   The table names for the <code>FROM</code> part of the {@see AbstractSet::getEntities()} method.
   */
  abstract protected function getEntitiesQuery($where = null, $orderBy = null);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getCount() {
    $result = $this->getMySQLi()->query("SELECT COUNT(*) FROM `{$this->getPluralKey()}` WHERE `deleted` = false LIMIT 1");
    $count  = $result->fetch_row()[0];
    $result->free();
    return $count;
  }

  /**
   * Get all entities that match the given filter.
   *
   * @param null|string $where [optional]
   *   The filtering <code>WHERE</code> clause.
   * @param null|string $orderBy [optional]
   *   The filtering <code>ORDER BY</code> clause.
   * @return null|array
   *   The desired entities, <code>NULL</code> if nothing matched the filter.
   */
  protected function getEntities($where = null, $orderBy = null) {
    $entities = null;
    $result   = $this->getMySQLi()->query($this->getEntitiesQuery($where, $orderBy));
    /* @var $entity \MovLib\Data\AbstractEntity */
    while ($entity = $result->fetch_object(substr(static::class, 0, -3), [ $this->diContainer ])) {
      $entities[$entity->id] = $entity;
    }
    $result->free();
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifiers(array $ids, $orderBy = null) {
    $ids = implode(",", $ids);
    if ($orderBy) {
      $orderBy = "ORDER BY {$orderBy}";
    }
    return $this->getEntities("WHERE `id` IN({$ids})", $orderBy);
  }

  /**
   * {@inheritdoc}
   */
  public function getOrdered($by, $offset, $limit) {
    return $this->getEntities("WHERE `deleted` = false", "ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}");
  }

  /**
   * {@inheritdoc}
   */
  public function getRandom() {
    $id     = null;
    $result = $this->getMySQLi()->query("SELECT `id` FROM `{$this->getPluralKey()}` WHERE `deleted` = false ORDER BY RAND() LIMIT 1");
    if ($result) {
      $id = $result->fetch_row()[0];
    }
    $result->free();
    return $id;
  }

}
