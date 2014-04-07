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
namespace MovLib\Data\Route;

/**
 * Defines the set route object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SetRoute {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The route arguments.
   *
   * @var array
   */
  public $args;

  /**
   * The route key.
   *
   * @var string
   */
  public $key;

  /**
   * The route in the current locale.
   *
   * @var string
   */
  public $url;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new route object.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param string $key
   *   The route's key.
   * @param array|string $args [optional]
   *   The route's arguments, defaults to <code>NULL</code>.
   */
  public function __construct(\MovLib\Core\Intl $intl, $key, $args = null) {
    $this->args = $args;
    $this->key  = $key;
    $this->url  = $intl->rp($key, $args);
  }

  /**
   * Get the route in the current locale.
   *
   * @return string
   *   The route in the current locale.
   */
  public function __toString() {
    return $this->url;
  }


}
