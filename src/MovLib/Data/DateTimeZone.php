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
 * Extended date time zone class with more utility methods to handle time zones.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DateTimeZone extends \DateTimeZone {

  /**
   * Get all sorted and translated time zone identifiers.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return array
   *   Associative array containing all sorted and translated time zone identifiers. The key is the time zone identifier
   *   and the value the translation for the currently active language. The array is sorted by value.
   */
  public static function getTranslatedIdentifiers() {
    global $i18n;
    $directory = __DIR__;
    require "{$directory}/TimeZoneTranslations/{$i18n->languageCode}.php";
    return $translatedTimeZoneIdentifiers;
  }

}
