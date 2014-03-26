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
 * Base class for all concrete formats.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormat {


  // ------------------------------------------------------------------------------------------------------------------- Constants



  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct() {

  }
  // @codeCoverageIgnoreEnd
  // @devEnd


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  abstract public function getAudioFormats();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get a list of all available aspect ratios.
   *
   * @return array
   *   List of all available aspect ratios.
   */
  final public function getAspectRatios() {
    return [
      "1.85:1",
      "2.35:1",
    ];
  }

  /**
   * Get a list of all available subtitles.
   *
   * @return array
   *   List of all available subtitles.
   */
  final public function getSubtitles() {
    return [

    ];
  }

  /**
   * Get a list of all available color formats.
   *
   * @return array
   *   The available color formats.
   *
   *   Associative array with the format name as key and the translation (if any) as value.
   *   being the tranlation.
   */
  final public function getColorFormats() {
    return [
      "Technicolor" => "Technicolor",
      "Eastmancolor" => "Eastmancolor",
      "Metrocolor" => "Metrocolor",
      "Fujicolor" => "Fujicolor",
      "Cinecolor" => "Cinecolor",
      "Pathécolor" => "Pathécolor",
      "Kinemacolor" => "Kinemacolor",
      "Black & White and Color" => $this->intl->t("Black & White and Color"),
      "Orwocolor" => "Orwocolor",
      "Sovcolor" => "Sovcolor",
      "Gevacolor" => "Gevacolor",
      "Ferraniacolor" => "Ferraniacolor",
      "Trucolor" => "Trucolor",
      "Agfacolor" => "Agfacolor",
      "Prizma" => "Prizma",
      "Warnercolor" => "Warnercolor",
      "DeLuxe Color" => "DeLuxe Color",
    ];
  }

}
