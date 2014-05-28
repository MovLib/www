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
namespace MovLib\Core\Entity\Genre;

use \MovLib\Core\Database\Database;

/**
 * Defines the base class for database set objects.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntitySet extends \MovLib\Core\Entity\AbstractEntitySet {


  /**
   * @deprecated
   */
  protected $entityClassName;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container);
    $this->entityClassName = substr(static::class, 0, -3);
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the query for {@see AbstractSet::getEntities()}.
   *
   * @deprecated
   * @param null|string $where [optional]
   *   The filtering <code>WHERE</code> clause.
   * @param null|string $orderBy [optional]
   *   The filtering <code>ORDER BY</code> clause.
   * @return string
   *   The query for {@see AbstractSet::getEntities()}.
   */
  abstract protected function getEntitiesQuery($where = null, $orderBy = null);

  /**
   * Get the query for {@see AbstractSet::loadEntitySets()}.
   *
   * <b>IMPORTANT</b><br>
   * The projection of the returned query has to include the unique identifier of each entity from the given set.
   * Otherwise it's impossible to create a mapping. Also note that this identifier has to be named
   * <code>"entityId"</code> because that's the property in the other class that is made for relational identifier.
   *
   * @deprecated
   * @param \MovLib\Data\AbstractEntitySet $set
   *   The set for which the query should return entities.
   * @param string $in
   *   The ready made content for the <code>IN</code> clause of the query.
   * @return string
   *   The query for {@see AbstractSet::loadEntitySets()}.
   */
  abstract protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all entities that match the given filter.
   *
   * @deprecated
   * @param null|string $where [optional]
   *   The filtering <code>WHERE</code> clause.
   * @param null|string $orderBy [optional]
   *   The filtering <code>ORDER BY</code> clause.
   * @return this
   * @throws \mysqli_sql_exception
   */
  protected function loadEntities($where = null, $orderBy = null) {
    $result = Database::getConnection()->query($this->getEntitiesQuery($where, $orderBy));
    /* @var $entity \MovLib\Data\AbstractEntity */
    while ($entity = $result->fetch_object($this->entityClassName, [ $this->container ])) {
      $this[$entity->id] = $entity;
    }
    $result->free();
    return $this;
  }

  /**
   * Initialize a given set with sets of this set.
   *
   * The above short description sounds funny, but it's the best description of what happens here. This method will load
   * sets of itself and export them with instances of it's controlled objects for each entity in your own set. You
   * should always use this method if you are dealing with a set that contains entity's which contain sets themselves
   * because it solves the <code>n+1</code> problem by fetching all entities for your entities in form of sets in a
   * single query.
   *
   * Still confused? Have a look at the implementation of this method!
   *
   * @link https://stackoverflow.com/questions/97197/what-is-the-n1-selects-issue
   * @param \MovLib\Data\AbstractEntitySet $set
   *   The set that contain entities which need sets with entities this set controls.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function loadEntitySets(\MovLib\Core\Entity\EntitySetInterface $set) {
    // Cast the set to a simple array, this allows us to use array functions.
    $setEntities = (array) $set;

    // Nothing to do if the passed set has no entities.
    if (empty($setEntities)) {
      return $this;
    }

    // Build the IN clause based on the loaded entities of the given set.
    $in = implode(",", array_keys($setEntities));

    // Try to load all entities for the given entities.
    $result = Database::getConnection()->query($this->getEntitySetsQuery($set, $in));

    $entitySetPropertyName = static::name;

    // Instantiate and export an instance of ourself to each entity of the passed set and seed those set instances with
    // the entities that were returned by the above query.
    /* @var $entity \MovLib\Data\AbstractEntity */
    while ($entity = $result->fetch_object($this->entityClassName, [ $this->container ])) {
      if (empty($set[$entity->entityId]->{$entitySetPropertyName})) {
        $set[$entity->entityId]->{$entitySetPropertyName} = new static($this->container);
      }
      $set[$entity->entityId]->{$entitySetPropertyName}[$entity->id] = $entity;
    }

    $result->free();
    return $this;
  }

  /**
   * Get all entities for the given unique identifiers.
   *
   * @deprecated
   * @param array $ids
   *   The unique identifiers to get the entities for.
   * @param string $orderBy [optional]
   *   Optional content for the <code>ORDER BY</code> SQL part, e.g. <code>"`created` DESC"</code>.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function loadIdentifiers(array $ids, $orderBy = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($ids), "The identifiers cannot be empty (check yourself before calling this method).");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $ids = implode(",", $ids);
    $orderBy && ($orderBy = "ORDER BY {$orderBy}");
    return $this->loadEntities("WHERE `{$this::$tableName}`.`id` IN({$ids})", $orderBy);
  }

  /**
   * Get all entities ordered and partitioned by the given parameters.
   *
   * @deprecated
   * @param string $by
   *   The SQL queries <code>ORDER BY</code> content, e.g. <code>"`created` DESC"</code>.
   * @param integer $offset
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param string $where [Optional]
   *   The WHERE clause of the SQL query (without WHERE).
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function loadOrdered($by, $offset, $limit, $where = null) {
    if ($where) {
     $where = " AND {$where}";
    }
    $this->loadEntities("WHERE `{$this::$tableName}`.`deleted` = false{$where}", "ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}");
    return $this;
  }

}
