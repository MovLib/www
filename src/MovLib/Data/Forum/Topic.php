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

/**
 * Defines the forum topic object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Topic {

  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Topic";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The topic's total post count.
   *
   * @var integer
   */
  public $countPosts;

  /**
   * The topic's unique identifier.
   *
   * @param integer
   */
  public $id;

  /**
   * The topic's parent entities.
   *
   * @param array
   */
  public $parents = [];

  /**
   * The topic's title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum topic.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @param integer $id [optional]
   *   The topic's unique identifier to load, defaults to <code>NULL</code> and an empty topic is created.
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {

  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


}
