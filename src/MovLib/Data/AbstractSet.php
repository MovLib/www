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
abstract class AbstractSet extends \MovLib\Data\AbstractConfig implements \Iterator, \MovLib\Data\PaginationInterface {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * The entities this set is currently working with.
   *
   * @var array
   */
  public $entities = [];

  /**
   * The canonical absolute class name of the entity this set controls.
   *
   * @var string
   */
  protected $entityClassName;

  /**
   * The short name of this set to access set properties in other entities to export itself.
   *
   * @var string
   */
  protected $shortName;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer) {
    parent::__construct($diContainer);
    $this->entityClassName = substr(static::class, 0, -3);
    $this->shortName       = lcfirst(basename(strtr(static::class, "\\", "/")));
    $this->init();
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the query for {@see AbstractSet::getEntities()}.
   *
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
   * @param \MovLib\Data\AbstractSet $set
   *   The set for which the query should return entities.
   * @param string $in
   *   The ready made content for the <code>IN</code> clause of the query.
   * @return string
   *   The query for {@see AbstractSet::loadEntitySets()}.
   */
  abstract protected function getEntitySetsQuery(\MovLib\Data\AbstractSet $set, $in);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @deprecated
   */
  public function getCount() {
    return $this->getTotalCount();
  }

  /**
   * Get a random, unique, existing entity's identifier from the set.
   *
   * @return mixed
   *   A random, unique, existing entity's identifier from the set.
   */
  public function getRandom() {
    $id     = null;
    $result = $this->getMySQLi()->query("SELECT `id` FROM `{$this->tableName}` WHERE `deleted` = false ORDER BY RAND() LIMIT 1");
    if ($result) {
      $id = $result->fetch_row()[0];
    }
    $result->free();
    return $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalCount() {
    // We use fetch all at this point, because we'll always get at least a single result from the query and it consumes
    // and closes the statement at once (lesser method calls).
    return (integer) $this->getMySQLi()->query(
      "SELECT COUNT(*) FROM `{$this->tableName}` WHERE `deleted` = false LIMIT 1"
    )->fetch_all()[0][0];
  }

  /**
   * Initialize the set.
   *
   * Further initialization work after an entity was initialized via PHP's built-in {@see \mysqli_result::fetch_object}
   * method or continued initialization after the own constructor was called.
   *
   * @return this
   */
  public function init() {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->singularKey), "You must set the \$singularKey property in your class " . static::class);
    assert(!empty($this->pluralKey), "You must set the \$pluralKey property in your class " . static::class);
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (empty($this->route)) {
      $this->route = $this->intl->r("/{$this->pluralKey}");
    }
    if (empty($this->tableName)) {
      $this->tableName = $this->pluralKey;
    }
    return $this;
  }

  /**
   * Get all entities that match the given filter.
   *
   * @param null|string $where [optional]
   *   The filtering <code>WHERE</code> clause.
   * @param null|string $orderBy [optional]
   *   The filtering <code>ORDER BY</code> clause.
   * @return this
   * @throws \mysqli_sql_exception
   */
  protected function loadEntities($where = null, $orderBy = null) {
    $result = $this->getMySQLi()->query($this->getEntitiesQuery($where, $orderBy));
    /* @var $entity \MovLib\Data\AbstractEntity */
    while ($entity = $result->fetch_object($this->entityClassName, [ $this->diContainer, null, false ])) {
      $this->entities[$entity->id] = $entity;
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
   * @param \MovLib\Data\AbstractSet $set
   *   The set that contain entities which need sets with entities this set controls.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function loadEntitySets(\MovLib\Data\AbstractSet $set) {
    // Nothing to do if the passed set has no entities.
    if (empty($set->entities)) {
      return $this;
    }

    // Build the IN clause based on the loaded entities of the given set.
    $in = implode(",", array_keys($set->entities));

    // Try to load all genres for the given entities.
    $result = $this->getMySQLi()->query($this->getEntitySetsQuery($set, $in));

    // Create a fresh instance of ourself that we can export to the entities of the given set in the upcoming loop.
    $reflector = new \ReflectionClass(static::class);

    // Instantiate and export an instance of ourself to each entity of the passed set and seed those set instances with
    // the entities that were returned by the above query.
    /* @var $entity \MovLib\Data\AbstractEntity */
    while ($entity = $result->fetch_object($this->entityClassName, [ $this->diContainer ])) {
      if (empty($set->entities[$entity->entityId]->{$this->shortName})) {
        $set->entities[$entity->entityId]->{$this->shortName} = $reflector->newInstance($this->diContainer);
      }
      $set->entities[$entity->entityId]->{$this->shortName}->entities[$entity->id] = $entity;
    }

    $result->free();
    return $this;
  }

  /**
   * Get all entities for the given unique identifiers.
   *
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
    return $this->loadEntities("WHERE `{$this->tableName}`.`id` IN({$ids})", $orderBy);
  }

  /**
   * Get all entities ordered and partitioned by the given parameters.
   *
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
    $this->loadEntities("WHERE `{$this->tableName}`.`deleted` = false{$where}", "ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}");
    return $this->entities;
  }


  // ------------------------------------------------------------------------------------------------------------------- Iterator Methods


  /**
   * Get the current entity.
   *
   * @return \MovLib\Data\AbstractEntity|boolean
   *   The current entity, or <code>FALSE</code> if there the array is empty or the current entity is invalid.
   */
  public function current() {
    return current($this->entities);
  }

  /**
   * Get the unique identifier of the current entity.
   *
   * @return mixed
   *   The unique identifier of the current entity, <code>NULL</code> if there are no entities or the current pointer is
   *   beyond the array's elements.
   */
  public function key() {
    return key($this->entities);
  }

  /**
   * Get the next entity.
   *
   * @return \MovLib\Data\AbstractEntity|boolean
   *   The next entity, or <code>FALSE</code> if there is no next entity.
   */
  public function next() {
    return next($this->entities);
  }

  /**
   * Rewind the iterator to the first entity.
   *
   * @return \MovLib\Data\AbstractEntity|boolean
   *   The first entity, or <code>FALSE</code> if there are no more entities.
   */
  public function rewind() {
    return reset($this->entities);
  }

  /**
   * Check if the current position is valid.
   *
   * @return boolean
   *   <code>TRUE</code> if the current position is valid, <code>FALSE</code> otherwise.
   */
  public function valid() {
    return key($this->entities) !== null;
  }

}
