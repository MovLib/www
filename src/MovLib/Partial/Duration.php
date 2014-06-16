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

use \MovLib\Core\Intl;

/**
 * Defines the duration object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Duration {

  /**
   * Get the seconds formatted as RFC 5545 (respectively ISO 8601) duration string.
   *
   * @link http://wiki.whatwg.org/wiki/Time_element#duration
   * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/common-microsyntaxes.html#valid-duration-string
   * @link http://tools.ietf.org/html/rfc5545
   * @link http://en.wikipedia.org/wiki/ISO_8601#Durations
   * @param integer $seconds
   *   The seconds to format.
   * @return string
   *   The formatted seconds.
   */
  public function format($seconds) {
    $duration = null;
    // Format the seconds according to RFC 5545 respectively ISO 8601.
    foreach ([ "D" => 86400, "H" => 3600, "M" => 60 ] as $type => $divisor) {
      $value    = floor($seconds / $divisor);
      $seconds -= $value * $divisor;
      if ($value > 0) {
        $duration .= "{$value}{$type}";
      }
      // The T has to be present, it doesn't matter if we have any days or not.
      if ($type === "D") {
        $duration .= "T";
      }
    }
    return "P{$duration}{$seconds}S";
  }

  /**
   * Get the seconds formatted as exact duration wrapped in a <code><time></code> element.
   *
   * @param integer $seconds
   *   The seconds to format.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the <code><time></code> element, note that <code>"datetime"</code>
   *   is always overwritten.
   * @param string $languageCode [optional]
   *   The language's ISO 639-1 alpha-2 code to format the duration, defaults to <code>NULL</code> and the current Intl
   *   language is used.
   * @return string
   *   The formatted seconds.
   * @throws \IntlException
   */
  public function formatExact($seconds, array $attributes = [], $languageCode = null) {
    $attributes           = new HTMLAttributes($attributes, $languageCode);
    $attributes->datetime = $this->format($seconds);
    $time                 = Intl::getInstance()->formatDuration($seconds, $languageCode);
    return "<time{$attributes}>{$time}</time>";
  }

  /**
   * Get the seconds rounded to minutes and wrapped in a <code><time></code> element.
   *
   * @param integer $seconds
   *   The seconds to format.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the <code><time></code> element, note that <code>"datetime"</code>
   *   is always overwritten.
   * @return string
   *   The formatted seconds.
   * @throws \IntlException
   */
  public function formatMinutes($seconds, array $attributes = [], $languageCode = null) {
    $attributes           = new HTMLAttributes($attributes, $languageCode);
    $attributes->datetime = $this->format($seconds);
    $time                 = Intl::getInstance()->t("{0,number,integer} min.", ceil($seconds / 60));
    return "<time{$attributes}>{$time}</time>";
  }

}
