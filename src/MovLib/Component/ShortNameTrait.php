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
namespace MovLib\Component;

/**
 * Defines the short name trait.
 *
 * While we have the <code>static::class</code> constant to access the fully qualified class name there's no such thing
 * for the short name. This ultra low-level base class extends an object and provides this functionality.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait ShortNameTrait {

  /**
   * Used to cache the class's short name.
   *
   * Do not use this property in your concrete class, always call one of the provided methods.
   *
   * @var null|string
   */
  protected static $shortName;

  /**
   * Get the short name of the class.
   *
   * @return string
   *   The short name of the class.
   */
  final public function shortName() {
    if (static::$shortName) {
      return static::$shortName;
    }
    return static::getShortName();
  }

  /**
   * Get the short name of the class.
   *
   * @return string
   *   The short name of the class.
   */
  final public static function getShortName() {
    if (!static::$shortName) {
      $shortName = explode("\\", static::class);
      static::$shortName = end($shortName);
    }
    return static::$shortName;
  }

}
