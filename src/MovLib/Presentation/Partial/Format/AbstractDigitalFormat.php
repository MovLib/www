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
namespace MovLib\Presentation\Partial\Format;

/**
 * Base class for all concrete formats.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractDigitalFormat extends \MovLib\Presentation\Partial\Format\AbstractFormat {


  // ------------------------------------------------------------------------------------------------------------------- Constants



  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct() {

  }
  // @codeCoverageIgnoreEnd
  // @devEnd


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  abstract public function getAspectRatios();

  abstract public function getAudioFormats();

  abstract public function getCodecs();

  abstract public function getTvNorms();

  abstract public function getRegionCodes();

  abstract public function getResolutions();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get a list of all available subtitles.
   *
   * @return array
   *   List of all available subtitles.
   */
  final public function getSubtitles() {
    static $subtitles = null;

    return $subtitles;
  }

}
