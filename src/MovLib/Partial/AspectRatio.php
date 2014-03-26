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
namespace MovLib\Partial;

/**
 * Static class defining all available aspect ratios.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AspectRatio {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * All available aspect ratios in international notation.
   *
   * @var array
   */
  private static $aspectRatios = [
    "1.85:1",
    "2.35:1",
  ];

  /**
   * All available aspect ratios formatted in the current locale.
   *
   * @var array
   */
  private static $formatted;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the formatted aspect ratio for the current locale.
   *
   * @param string $aspectRatio
   *   The desired aspect ratio in default locale notation.
   * @return string
   *   The formatted aspect ratio for the current locale.
   */
  final public static function get($aspectRatio) {
    if (!self::$formatted) {
      return self::getAspectRatios()[$aspectRatio];
    }
    return self::$formatted[$aspectRatio];
  }

  /**
   * All available aspect ratios in the current locale.
   *
   * @todo Generate formatted aspect ratios for each system language offline, like with the other stuff.
   * @return array
   *   All available aspect ratios in the current locale.
   */
  final public static function getAspectRatios() {
    if (!self::$formatted) {
      $c = count(self::$aspectRatios);
      for ($i = 0; $i < $c; ++$i) {
        self::$formatted[self::$aspectRatios[$i]] = $this->intl->format(
          "{0,number}:{1,number,integer}",
          explode(":", self::$aspectRatios[$i], 2)
        );
      }
    }
    return self::$formatted;
  }

}
