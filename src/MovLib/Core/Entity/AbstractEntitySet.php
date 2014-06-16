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
namespace MovLib\Core\Entity;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Database\Query\Select;
use \MovLib\Core\Routing\Route;

/**
 * Defines the entity set base class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntitySet extends \ArrayObject implements \MovLib\Core\Entity\EntitySetInterface {
  use \MovLib\Component\ArrayObjectTrait;
  use \MovLib\Core\Entity\EntityTrait;
  use \MovLib\Core\Routing\RoutingTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractEntitySet";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entities untranslated bundle title.
   *
   * @var string
   */
  private $entityBundle;

  /**
   * The entities class.
   *
   * @var string
   */
  private $entityClass;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new entity set.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container, $bundlePlural, $bundleSingular, $bundleTitle) {
    // @devStart
    // @codeCoverageIgnoreStart
    // We have no chance to find these values based on our surroundings, the concrete set has to set them.
    //
    // @todo Is there any way we might be able to abstract this?
    assert(isset(static::$tableName), "You have to set the static \$tableName property in your set.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct([]);

    // Export the container and intl to their own properties. We can't abstract this into the trait because we're
    // extending \ArrayObject.
    $this->container = $container;
    $this->intl      = $container->intl;

    $this->bundle       = $bundlePlural;
    $this->bundleTitle  = $bundleTitle;
    $this->entityBundle = $bundleSingular;

    // We can build the entity's class based on our own.
    $this->entityClass = substr(static::class, 0, -3);

    if (!$this->route) {
      $routeKey    = strtolower($this->bundle);
      $this->route = new Route("/{$routeKey}");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Add projection, joins, and additional conditions to the select query.
   *
   * @param \MovLib\Core\Database\Query\Select $select
   *   The prepared select query.
   * @return \MovLib\Core\Database\Query\Select $select
   *   The select query ready for execution.
   */
  abstract protected function doLoad(\MovLib\Core\Database\Query\Select $select);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  final public function bundleTitle($locale) {
    return $this->intl->tp(-1, $this->bundle, $this->entityBundle, null, $locale);
  }

  /**
   * {@inheritdoc}
   */
  public function load(\MovLib\Core\Database\Query\Condition $conditions = null, $alias = null) {
    // Prepare extendable database select query object.
    $select = (new Select(Database::getConnection(), static::$tableName, $alias));

    // Only add the conditions if we have any.
    if ($conditions) {
      $select->setConditions($conditions);
    }

    // Allow the concrete class to alter the object.
    $this->doLoad($select);

    // Execute the select query and fetch all objects from the query. We exchange our current instance's array with the
    // new array. This ensures that we have exactly the entities within us that were requested, in case someone called
    // this method multiple times.
    $this->exchangeArray($select->fetchObjects($this->entityClass, [ $this->container ]));

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRandom() {
    // A concrete set which has to return a different value (user) can simply overwrite it.
    $result = Database::getConnection()->query(
      "SELECT `id` FROM `{$this::$tableName}` WHERE `deleted` = 0 ORDER BY RAND() LIMIT 1"
    )->fetch_all();

    // Result may be NULL in case the tables is completely empty.
    if ($result) {
      return $result[0][0];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalCount() {
    return (integer) Database::getConnection()->query("SELECT COUNT(*) FROM `{$this::$tableName}` WHERE `deleted` = 0 LIMIT 1")->fetch_all()[0][0];
  }

}
