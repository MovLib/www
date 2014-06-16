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

/**
 * Defines the entity trait.
 *
 * The entity trait provides properties and methods that are shared in both, the entity and entity set, base classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait EntityTrait {


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * The entity's primary table name.
   *
   * @internal We keep this property public and share it between the set and the entity.
   * @var string
   */
  public static $tableName;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity's / set's untranslated bundle title.
   *
   * @var string
   */
  public $bundle;

  /**
   * The entity's / set's translated bundle title.
   *
   * @var string
   */
  public $bundleTitle;

  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\Container
   */
  protected $container;

  /**
   * Active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The entity's / set's parents.
   *
   * @var array
   */
  public $parents = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @see \MovLib\Core\Entity\EntityInterface::__toString()
   * @see \MovLib\Core\Entity\EntitySetInterface::__toString()
   */
  final public function __toString() {
    return static::name;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @see \MovLib\Core\Entity\EntityInterface::parents()
   * @see \MovLib\Core\Entity\EntitySetInterface::parents()
   */
  final public function parents() {
    return $this->parents;
  }

  /**
   * @see \MovLib\Core\Entity\EntityInterface::route()
   * @see \MovLib\Core\Entity\EntitySetInterface::route()
   */
  final public function route($locale) {
    return $this->route->recompile($locale);
  }

}
