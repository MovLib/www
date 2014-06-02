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
namespace MovLib\Core\Diff;

/**
 * Defines the diff object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Diff {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Diff";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Apply transformations to a string as computed by {@see Diff::getPatch()}.
   *
   * @link https://github.com/cogpowered/FineDiff/
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

    $output      = null;
    $patchLength = mb_strlen($patch);
    $fromOffset  = $patchOffset = 0;

    while ($patchOffset < $patchLength) {
      $transformation = mb_substr($patch, $patchOffset, 1);
      ++$patchOffset;
      $n = (integer) mb_substr($patch, $patchOffset);

      if ($n) {
        $patchOffset += strlen((string) $n);
      }
      else {
        $n = 1;
      }

      // Since we only use text, we can ignore all operations except copy and insert.
      if ($transformation == "c") {
        // Copy $n characters from the original string.
        $output .= mb_substr($from, $fromOffset, $n);
        $fromOffset += $n;
      }
      // Insert $n characters from the patch.
      elseif ($transformation == "i") {
        $output .= mb_substr($patch, ++$patchOffset, $n);
        $patchOffset += $n;
      }
      // Ignore $n characters for other operations.
      else {
        $fromOffset += $n;
      }
    }

    return $output;
  }

  /**
   * Get the diff patch to recreate <var>$old</var> from <var>$new</var>.
   *
   * @param string $new
   *   The new string.
   * @param string $old
   *   The old string.
   * @return string
   *   The diff patch that can be used with {@see Diff::applyPatch()} to recreated <var>$old</var> from <var>$new</var>.
   */
  public function getPatch($new, $old) {
    // Our own implementation of the diff contained errors, we therefore fall back to the implementation from FineDiff.
    //
    // @todo Revisit this problem and create an efficient solution for our use case.
    $fineDiff = new \cogpowered\FineDiff\Diff();

    // Note that the Parser class of our FineDiff is patched to use mb_ functions!
    return (string) $fineDiff->getOpcodes($new, $old);
  }

}
