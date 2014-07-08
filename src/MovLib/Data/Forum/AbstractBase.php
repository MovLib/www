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
namespace MovLib\Data\Forum;

use \MovLib\Core\Routing\RoutingTrait;
use \MovLib\Component\String;

/**
 * Defines the base class for all forum classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase implements \MovLib\Core\Routing\RoutingInterface {
  use RoutingTrait {
    RoutingTrait::setRoute as private traitSetRoute;
  }


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractBase";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\Container
   */
  protected $container;

  /**
   * The concrete object's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The internationalization instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The concrete object's parent entities.
   *
   * @var array
   */
  public $parents = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum object.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   */
  public function __construct(\MovLib\Core\Container $container) {
    $this->container = $container;
    $this->intl      = $container->intl;
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  public static function getInstance(\MovLib\Core\Container $container, $id) {
    $name  = strtolower(static::name);
    $cache = $container->getPersistentCache("forum-{$name}-{$id}");
    if (($instance = $cache->get()) === null) {
      $instance = new static($container, $id);
    }
    return $instance;
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get cache key.
   *
   * @param string $suffix
   *   The suffix to append to the cache key.
   * @return string
   *   The complete cache key.
   */
  abstract protected function getCacheKey($suffix);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the concrete object's route.
   *
   * @param \MovLib\Core\Intl $intl
   *   The internationalization instance for route translation.
   * @param \MovLib\Data\Forum\Forum $forum
   *   The concrete object's parent forum.
   * @param string $path
   *   The concrete object's untranslated route path.
   * @param array $args [optional]
   *   The route's formatting arguments, note the forum's title is always prepended to the arguments and thus shouldn't
   *   be included in the passed arguments array.
   * @return this
   */
  public function setRoute(\MovLib\Core\Intl $intl, \MovLib\Data\Forum\Forum $forum, $path, array $args = []) {
    array_unshift($args, String::sanitizeFilename($forum->title));
    $this->route = new Route($intl, $forum, $path, [ "args" => $args ]);
    return $this;
  }

}
