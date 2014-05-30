<?php

/* !
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
namespace MovLib\Core\Diff;

/**
 * Defines the xdiff object.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class XDiff {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "XDiff";

  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Apply transformations to a string as computed by {@see XDiff::getPatch()}.
   *
   * @param string $from
   *   The string to apply the tranformations to.
   * @param string $patch
   *   The transformations to apply.
   * @return string
   *   The patched string.
   */
  public function applyPatch($from, $patch) {
    // Do nothing if the patch is empty, the from string already has the correct form.
    if (empty($patch)) {
      return $from;
    }

    // Let xdiff do its magic.
    return xdiff_string_bpatch($from, $patch);
  }

  /**
   * Get the differences between two strings as binary diff.
   *
   * The returned diff patch can be used together with the {@see XDiff::applyPatch()} method to recreate the
   * <var>$from</var> string to the <var>$to</var> string at a later point. The patch is an optimized transformation
   * computed by xdiff.
   *
   * @param string $from
   *   The old string.
   * @param string $to
   *   The new string.
   * @return string
   *   The binary patch representing the differences.
   */
  public function getPatch($from, $to) {
    return xdiff_string_bdiff($from, $to);
  }

}
