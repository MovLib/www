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
namespace MovLib\Partial\Format;

/**
 * Factory for instantiating media formats.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class FormatFactory {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Blu-ray media format.
   *
   * @var integer
   */
  const FORMAT_BLU_RAY = "BD";


  /**
   * DVD media format.
   *
   * @var integer
   */
  const FORMAT_DVD = "DVD";

  /**
   * Film reel media format.
   *
   * Includes film reels nowadays but also weird formats of the pre-cinema era since they could only be viewed publicly.
   *
   * @var integer
   */
  const FORMAT_FILM_REEL = "Film Reel";


  // ------------------------------------------------------------------------------------------------------------------- Constants


  private static $formatClasses = [
    self::FORMAT_CINEMA => "\\MovLib\\Presentation\\Partial\\Format\\Cinema",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the partial for the given format type.
   *
   * @param integer $formatType
   *   One of the <code>FormatFactory::FORMAT_*</code> constants.
   * @return \MovLib\Presentation\Partial\Format\AbstractFormat
   *   The partial for the given format type.
   */
  public static function getFormat($formatType) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset(self::$formatClasses[$formatType])) {
      throw new \InvalidArgumentException("\$formatType must be one of FormatFactory::FORMAT_* constants.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return new self::$formatClasses[$formatType]();
  }

}
