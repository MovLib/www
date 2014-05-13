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
 * Defines the revision object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Revision {

  /**
   * The translated name of the revision's entity.
   *
   * @var string
   */
  public $name;

  /**
   * The translated route of the entity.
   *
   * @var string
   */
  public $route;

  /**
   * The translated type of the revision's entity, e.g. Movie.
   *
   * @var string
   */
  public $type;


  /**
   * Instantiate a new Revision object.
   *
   * @param string $name
   *   The translated name of the revision's entity.
   * @param string $route
   *   The translated route of the entity
   * @param string $type
   *   The translated type of the revision's entity, e.g. Movie.
   * @return this
   */
  public function __construct($name, $route, $type) {
    $this->name  = $name;
    $this->route = $route;
    $this->type  = $type;
    return $this;
  }

}
