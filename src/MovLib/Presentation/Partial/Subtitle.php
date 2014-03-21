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
namespace MovLib\Presentation\Partial;

use \MovLib\Data\FileSystem;

/**
 * Represents a single subtitle in HTML and provides an interface to all available subtitles in the current locale.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Subtitle {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array containing all available subtitles in the current locale.
   *
   * @var array
   */
  protected static $subtitles;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get subtitle instance.
   *
   * @param string $code
   *   The ISO 639-1 code of the subtitle. Additionally you can pass one of the following:
   *   <ul>
   *     <li><code>""</code> for a commentary subtitle</li>
   *     <li><code>""</code> for a movie fact subtitle</li>
   *     <li><code>"xx"</code> for other subtitles</li>
   *   </ul>
   * @param boolean|null $closed [optional]
   *   Whether this subtitle is closed or not, defaults to <code>NULL</code> which indicates that this subtitle doesn't
   *   support closed.
   * @param boolean $forced [optional]
   *   Whether this subitlte is forced or not, defaults to <code>TRUE</code> which means that the subtitle is burnt-in.
   * @return \MovLib\Stub\Data\Subtitle
   *   Subtitle instance.
   */
  public static function get($code, $closed = null, $forced = null) {
    if (!self::$subtitles) {
      $subtitle = self::getSubtitles()[$code];
    }
    else {
      $subtitle = self::$subtitles[$code];
    }
    $subtitle->closed = $closed;
    $subtitle->forced = $forced;
    return $subtitle;
  }

  /**
   * Get all available subtitles.
   *
   * @return array
   *   All available subtitles.
   */
  public static function getSubtitles() {
    if (!self::$subtitles) {
      self::$subtitles = require FileSystem::realpath("i18n://subtitles");
    }
    return self::$subtitles;
  }

}
