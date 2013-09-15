<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * Same as PHP's built in collator.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Collator extends \Collator {

  /**
   * Sorts an array by key, maintaining key to data correlations. This is useful for associative arrays.
   *
   * @todo Pretty stupid and fast forward approach. Hopefully this will be included in future Intl releases as a native
   *       method. Anybody who's interested in optimizing this is very welcome.
   * @see ksort()
   * @param array $array
   *   The input array.
   * @param int $sort_flags
   *   [Optional] Sorting type, one of the following:
   *   <ul>
   *     <li><var>Collator::SORT_REGULAR</var> - compare items normally (don't change types)</li>
   *     <li><var>Collator::SORT_NUMERIC</var> - compare items numerically</li>
   *     <li><var>Collator::SORT_STRING</var> - compare items as strings</li>
   *   </ul>
   *   Default sorting type is <var>Collator::SORT_REGULAR</var>. It is also used if an invalid <em>sort_flag</em> value
   *   has been specified.
   * @return boolean
   *   Returns <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function ksort(&$array, $sort_flags = \Collator::SORT_REGULAR) {
    $original = $array;
    $sorted = array_keys($array);
    if (!$this->sort($sorted, $sort_flags)) {
      return false;
    }
    $array = [];
    foreach ($sorted as $delta => $key) {
      $array[$key] = $original[$key];
    }
    return true;
  }

}
