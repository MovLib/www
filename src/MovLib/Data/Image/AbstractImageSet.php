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
namespace MovLib\Data\Image;

/**
 * Defines the abstract image set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImageSet extends \MovLib\Data\AbstractSet {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity's unique identifier this set belongs to.
   *
   * An image set always contains image's that belong to a single entity. Use the {@see \MovLib\Data\Upload\UploadSet}
   * to list images without this restriction.
   *
   * @var mixed
   */
  protected $entityId;

  /**
   * The column name of the entity where the unique identifier is stored (without <code>"_id"</code> suffix).
   *
   * @var string
   */
  protected $entityKey;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image set.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param mixed $entityId
   *   The unique identifier of the entity the images of this set belong to.
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $entityId) {
    parent::__construct($diContainer);
    // @devStart
    // @codeCoverageIgnoreStart
    assert(is_numeric($entityId), "An image set's unique entity identifier has to be numeric.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->entityId = $entityId;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  // @devStart
  // @codeCoverageIgnoreStart
  protected function init() {
    assert(!empty($this->entityKey), "You have to set the \$entityKey property in your image set.");
    return parent::init();
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * {@inheritdoc}
   */
  public function getCount() {
    $result = $this->getMySQLi()->query(<<<SQL
SELECT COUNT(*) FROM `{$this->tableName}` WHERE `deleted` = false AND `{$this->entityKey}_id` = {$this->entityId} LIMIT 1
SQL
    );
    $count  = $result->fetch_row()[0];
    $result->free();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getRandom() {
    $id     = null;
    $result = $this->getMySQLi()->query(<<<SQL
SELECT `id` FROM `{$this->tableName}` WHERE `deleted` = false AND `{$this->entityKey}_id` = {$this->entityId} ORDER BY RAND() LIMIT 1
SQL
    );
    if ($result) {
      $id = $result->fetch_row()[0];
    }
    $result->free();
    return $id;
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
    $where .= (empty($where) ? " WHERE " : " AND ") . "`{$this->entityKey}_id` = {$this->entityId}";
    $result = $this->getMySQLi()->query($this->getEntitiesQuery($where, $orderBy));
    /* @var $entity \MovLib\Data\AbstractEntity */
    while ($entity = $result->fetch_object($this->entityClassName, [ $this->diContainer, $this->entityId ])) {
      $this->entities[$entity->id] = $entity;
    }
    $result->free();
    return $this;
  }

}
