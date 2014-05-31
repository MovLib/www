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
   * Get the differences between two strings as transformations.
   *
   * The returned diff patch can be used together with the {@see Diff::applyPatch()} method to recreate the
   * <var>$from</var> string to the <var>$to</var> string at a later point. The transformations are optimized for size,
   * the computation of the differences can take a long time if the strings are long.
   *
   * @param string $from
   *   The old string.
   * @param string $to
   *   The new string.
   * @return string
   *   The difference between two strings as transformations.
   */
  public function getPatch($from, $to) {
    // Initialize all variables needed beforehand and add the first parse job.
    $result     = [];
    $jobs       = [[ 0, mb_strlen($from), 0, mb_strlen($to) ]];
    $copyLength = $fromCopyStart = $toCopyStart = 0;

    // Pop the next job from the stack.
    while ($job = array_pop($jobs)) {
      list($fromSegmentStart, $fromSegmentEnd, $toSegmentStart, $toSegmentEnd) = $job;

      $fromSegmentLength = $fromSegmentEnd - $fromSegmentStart;
      $toSegmentLength   = $toSegmentEnd - $toSegmentStart;

      // Detect simple insert/delete operations and continue with next job.
      if ($fromSegmentLength === 0 || $toSegmentLength === 0) {
        if ($fromSegmentLength > 0) {
          $deleteLength = $fromSegmentLength === 1 ? null : $fromSegmentLength;
          $result[$fromSegmentStart * 4 + 0] = "d{$deleteLength}";
        }
        else {
          $insertText   = mb_substr($to, $toSegmentStart, $toSegmentLength);
          $insertLength = mb_strlen($insertText);
          $insertLength = $insertLength === 1 ? null : $insertLength;
          $result[$fromSegmentStart * 4 + 1] = "i{$insertLength}:{$insertText}";
        }
        continue;
      }

      // Determine start and length for a copy transformation.
      if ($fromSegmentLength >= $toSegmentLength) {
        $copyLength = $toSegmentLength;

        while ($copyLength) {
          $toCopyStartMax = $toSegmentEnd - $copyLength;

          for ($toCopyStart = $toSegmentStart; $toCopyStart <= $toCopyStartMax; ++$toCopyStart) {
            $fromCopyStart = mb_strpos(
              mb_substr($from, $fromSegmentStart, $fromSegmentLength),
              mb_substr($to, $toCopyStart, $copyLength)
            );

            if ($fromCopyStart !== false) {
              $fromCopyStart += $fromSegmentStart;
              break 2;
            }
          }

          --$copyLength;
        }
      }
      else {
        $copyLength = $fromSegmentLength;

        while ($copyLength) {
          $fromCopyStartMax = $fromSegmentEnd - $copyLength;

          for ($fromCopyStart = $fromSegmentStart; $fromCopyStart <= $fromCopyStartMax; ++$fromCopyStart) {
            $toCopyStart = mb_strpos(
              mb_substr($to, $toSegmentStart, $toSegmentLength),
              mb_substr($from, $fromCopyStart, $copyLength)
            );

            if ($toCopyStart !== false) {
              $toCopyStart += $toSegmentStart;
              break 2;
            }
          }

          --$copyLength;
        }
      }

      // A copy operation is possible.
      if ($copyLength) {
        $copyTranformationLength = $copyLength === 1 ? null : $copyLength;
        $result[$fromCopyStart * 4 + 2] = "c{$copyTranformationLength}";
        // Add new jobs for the parts of the segment before and after the copy part.
        $jobs[] = [ $fromSegmentStart, $fromCopyStart, $toSegmentStart, $toCopyStart ];
        $jobs[] = [ $fromCopyStart + $copyLength, $fromSegmentEnd, $toCopyStart + $copyLength, $toSegmentEnd ];
      }
      // No copy possible, replace everything.
      else {
        $deleteLength = $fromSegmentLength === 1 ? null : $fromSegmentLength;
        $insertLength = $toSegmentLength === 1 ? null : $toSegmentLength;
        $insertText   = mb_substr($to, $toSegmentStart, $toSegmentLength);
        $result[$fromSegmentStart * 4] = "d{$deleteLength}i{$insertLength}:{$insertText}";
      }
    }

    // Sort and return the string representation of the transformations.
    ksort($result, SORT_NUMERIC);
    return implode(array_values($result));
  }

}
