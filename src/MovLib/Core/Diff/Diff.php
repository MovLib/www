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


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * Code for copy transformations.
   *
   * @var string
   */
  const COPY = "c";

  /**
   * Code for copy key.
   *
   * @var integer
   */
  const COPY_KEY = 0;

  /**
   * Deadline for for diff generation.
   *
   * @var float
   */
  const DEADLINE = 1.0;

  /**
   * Code for delete transformations.
   *
   * @var string
   */
  const DELETE = "d";

  /**
   * Code for delete key.
   *
   * @var integer
   */
  const DELETE_KEY = -1;

  /**
   * Code for insert transformations.
   *
   * @var string
   */
  const INSERT = "i";

  /**
   * Code for insert key.
   *
   * @var integer
   */
  const INSERT_KEY = 1;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Apply patch to a string and recreate a previous version of this string.
   *
   * @link https://github.com/cogpowered/FineDiff/
   * @param string $string
   *   The string to apply the patch to.
   * @param string $patch
   *   The patch to apply.
   * @return string
   *   The patched string.
   */
  public function applyPatch($string, $patch) {
    // Do nothing if the patch is empty, the from string already has the correct form.
    if (empty($patch)) {
      return $string;
    }

    // Prepare variables for patching.
    $output       = null;
    $patchLength  = mb_strlen($patch);
    $stringOffset = $patchOffset = 0;

    // Patch as long as there are transformations available.
    while ($patchOffset < $patchLength) {
      // Pop the next transformation code from the string. Note the usage of the non-multi-byte substr function at this
      // point; a transformation code is always a non-multi-byte character.
      $transformation = substr($patch, $patchOffset, 1);

      // Increase the patch offset, we just consumed a character.
      ++$patchOffset;

      // Determine if we have to jump over some more transformation instructions. An instruction may be followed by an
      // integer that tells us how many times we have to perform that transformation. We get that integer by simply
      // casting the substring to an integer value. PHP will drop all the characters after the first non-numeric one.
      //
      // E.g.: 234foo = 234
      if (($n = (integer) mb_substr($patch, $patchOffset))) {
        // We need to increase the patch offset in case we consumed something from the patch. Again note the usage of
        // the non-multi-byte function strlen at this point. We only consumed integers which are alway ASCII. The cast
        // to string of $n is necessary because we want to know how many characters we consumed in the original string,
        // we aren't interested in the actual integer at this point.
        $patchOffset += strlen((string) $n);
      }
      // The transformation doesn't include any repetition count.
      else {
        $n = 1;
      }

      // Apply the transformation that we just extracted.
      switch ($transformation) {
        // Insert $n characters from the patch.
        case self::INSERT:
          // We have to increase the patch offset by one to jump over the colon delimiter.
          $output      .= mb_substr($patch, ++$patchOffset, $n);
          $patchOffset += $n;
          break;

        // Copy $n characters from the original string.
        case self::COPY:
          $output .= mb_substr($string, $stringOffset, $n);
          // no break

        // Ignore $n characters for delete operations.
        //case self::DELETE:
        default:
          $stringOffset += $n;
          // no break
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


  // ------------------------------------------------------------------------------------------------------------------- NEW


  /**
   * Get the diff patch to recreate <var>$old</var> from <var>$new</var>.
   *
   * This method is optimized for serialized PHP strings that contain changes. Our form system has to make sure that
   * there are actually changes because we aren't able to determine this within our revision system. The problem is that
   * new revision always contain changes due to the fact that the created date and time is set to the date and time of
   * the request. The fact that we don't even want to start the revisioning process if the user hasn't changed anything
   * lead to the decision that the form system should take care of this case.
   *
   * <b>NOTE</b><br>
   * This method has all operations inlined for better speed. This makes it somewhat hard to follow. Have a look at the
   * original implementation to better understand what's going on. I inserted some visual delimiters to make it easier
   * to read.
   *
   * <b>SIDENOTE</b><br>
   * We aren't using the FineDiff implementation to generate the patches because it's awfully slow compared to Google's
   * diff-match-patch algorithm family. We are using it to apply patches though, because the produced diff patches are
   * extremely small, human readable and easy to apply.
   *
   * @link https://github.com/yetanotherape/diff-match-patch
   * @see Diff::applyPatch()
   * @param string $new
   *   The newer serialized PHP string.
   * @param string $old
   *   The older serialized PHP string.
   * @return string
   *   The diff patch to recreate <var>$old</var> from <var>$new</var>.
   */
  public function getPatchOptimizedForRevisions($new, $old) {
    // -----------------------------------------------------------------------------------------------------------------
    // We always have common prefixes because we're always creating patches for serialized strings of the same objects.

    $newLength = mb_strlen($new);
    $oldLength = mb_strlen($old);
    $prefixMin = $prefixStart = 0;
    $prefixMax = $prefixMid   = min($newLength, $oldLength);

    // Binary search...
    while ($prefixMin < $prefixMid) {
      // Compute length once...
      $len = $prefixMid - $prefixStart;

      if (mb_substr($new, $prefixStart, $len) == mb_substr($old, $prefixStart, $len)) {
        $prefixStart = $prefixMin = $prefixMid;
      }
      else {
        $prefixMax = $prefixMid;
      }

      $prefixMid = (integer) (($prefixMax - $prefixMin) / 2) + $prefixMin;
    }

    // Remember the prefix copy operation.
    $prefix = self::COPY . ($prefixMid === 1 ? null : $prefixMid);

    // -----------------------------------------------------------------------------------------------------------------
    // We always have common suffixes because of the same reason as above.

    // Simple substraction is faster than calling mb_strlen() again!
    $newLength -= $prefixStart;
    $oldLength -= $prefixStart;
    $suffixMin = $suffixEnd = 0;
    $suffixMax = $suffixMid = min($newLength, $oldLength);

    // Binary search..
    while ($suffixMin < $suffixMid) {
      // Compute length once...
      $len = $suffixMid - $suffixEnd;

      if (mb_substr($new, -$suffixMid, $len) == mb_substr($old, -$suffixMid, $len)) {
        $suffixEnd = $suffixMin = $suffixMid;
      }
      else {
        $suffixMax = $suffixMid;
      }

      $suffixMid = (integer) (($suffixMax - $suffixMin) / 2) + $suffixMin;
    }

    // Remember the suffix copy operation.
    $suffix = self::COPY . ($suffixMid === 1 ? null : $suffixMid);

    // -----------------------------------------------------------------------------------------------------------------
    // Remove the common pre- and suffixes from both serialized strings.

    // Simple substraction is faster than calling mb_strlen() again and the following calls to mb_substr() are faster.
    $newLength -= $suffixEnd;
    $oldLength -= $suffixEnd;
    $new        = mb_substr($new, $prefixStart, $newLength);
    $old        = mb_substr($old, $prefixStart, $oldLength);

    // -----------------------------------------------------------------------------------------------------------------
    // The actual diff of the middle block is generated with the default implementation.

    // @todo We have to add prefix and suffix to the diff for clean up!
    $diff = $this->getDiff($new, $old, null, $newLength, $oldLength);

    // -----------------------------------------------------------------------------------------------------------------
    // Now we start creating the actual patch string, again optimized / inlined for speed.

    $patch = null;
    $colon = ":";
    $c     = count($diff);
    for ($i = 0; $i < $c; ++$i) {
      if ($diff[$i][0] === self::INSERT_KEY) {
        $patch .= self::INSERT . ($diff[$i][2] === 1 ? null : $diff[$i][2]) . $colon . $diff[$i][1];
      }
      else {
        if ($diff[$i][0] === self::COPY_KEY) {
          $patch .= self::COPY;
        }
        else {
          $patch .= self::DELETE;
        }
        $patch .= $diff[$i][2] === 1 ? null : $diff[$i][2];
      }
    }

    return "{$prefix}{$patch}{$suffix}";
  }

  /**
   *
   * @param type $text1
   * @param type $text2
   * @param float $deadline [internal]
   *   Used internally for recursive calls.
   * @param type $text1Length [internal]
   *   Used internally for recursive calls.
   * @param type $text2Length [internal]
   *   Used internally for recursive calls.
   * @return type
   */
  public function getDiff($text1, $text2, $deadline = null, $text1Length = null, $text2Length = null) {
    // Calculate deadline for diff patch generation.
    if ($deadline === null) {
      $deadline = microtime(true) + self::DEADLINE;
    }

    // Compute lengths if not passed.
    $text1Length || ($text1Length = mb_strlen($text1));
    $text2Length || ($text2Length = mb_strlen($text2));

    // Check for equality.
    if ($text1 == $text2) {
      if ($text1 == "") {
        return [];
      }
      return [[ self::COPY_KEY, $text1, $text1Length ]];
    }

    // Compute common prefix.
    $prefixLength = $this->commonPrefix($text1, $text1Length, $text2, $text2Length);
    if ($prefixLength === 0) {
      $prefix = null;
    }
    else {
      $text1Length -= $prefixLength;
      $text2Length -= $prefixLength;
      $text1        = mb_substr($text1, $prefixLength, $text1Length);
      $text2        = mb_substr($text2, $prefixLength, $text2Length);
      $prefix       = mb_substr($text1, 0, $prefixLength);
    }

    // Compute common suffix.
    $suffixLength = $this->commonSuffix($text1, $text1Length, $text2, $text2Length);
    if ($suffixLength === 0) {
      $suffix = null;
    }
    else {
      $text1Length -= $suffixLength;
      $text2Length -= $suffixLength;
      $text1        = mb_substr($text1, 0, $text1Length);
      $text2        = mb_substr($text2, 0, $text2Length);
      $suffix       = mb_substr($text1, -$suffixLength);
    }

    // Compute diff of middle block.
    $diffs = $this->compute($text1, $text1Length, $text2, $text2Length, $deadline);

    // Restore common prefix.
    if ($prefix) {
      array_unshift($diffs, [ self::COPY_KEY, $prefix, mb_strlen($prefix) ]);
    }

    // Append common suffix.
    if ($suffix) {
      $diffs[] = [ self::COPY_KEY, $suffix, mb_strlen($suffix) ];
    }

    // Clean the diff by merging as many operations as possible.
    return $this->cleanUpDiff($diffs);
  }

  /**
   *
   * @param array $diffs
   * @return array
   */
  protected function cleanUpDiff($diffs) {
    $diffs[] = [ self::COPY_KEY, null, 0 ];

    $pointer    = $countDelete = $countInsert = $lengthDelete = $lengthInsert = 0;
    $textDelete = $textInsert  = null;

    while ($pointer < count($diffs)) {
      if ($diffs[$pointer][0] === self::INSERT_KEY) {
        $textInsert   .= $diffs[$pointer][1];
        $lengthInsert += $diffs[$pointer][2];
        ++$countInsert;
        ++$pointer;
      }
      elseif ($diffs[$pointer][0] === self::DELETE_KEY) {
        $textDelete   .= $diffs[$pointer][1];
        $lengthDelete += $diffs[$pointer][2];
        ++$countDelete;
        ++$pointer;
      }
      else/*if ($diffs[$pointer][0] === self::COPY_KEY)*/ {
        // Check for prior redundancies.
        if ($countDelete + $countInsert > 1) {
          if ($countDelete !== 0 && $countInsert !== 0) {
            // Factor out common prefixes.
            $prefix = $this->commonPrefix($textInsert, $lengthInsert, $textDelete, $lengthDelete);
            if ($prefix !== 0) {
              $x = $pointer - $countDelete - $countInsert - 1;
              if ($x >= 0 && $diffs[$x][0] === self::COPY_KEY) {
                $diffs[$x][1] .= mb_substr($textInsert, 0, $prefix);
                $diffs[$x][2] += $prefix;
              }
              else {
                array_unshift($diffs, [ self::COPY_KEY, mb_substr($textInsert, 0, $prefix), $prefix ]);
                ++$pointer;
              }
              $textInsert    = mb_substr($textInsert, $prefix);
              $lengthInsert -= $prefix;
              $textDelete    = mb_substr($textDelete, $prefix);
              $lengthDelete -= $prefix;
            }

            // Factor our common suffixes.
            $suffix = $this->commonSuffix($textInsert, $lengthInsert, $textDelete, $lengthDelete);
            if ($suffix !== 0) {
              $diffs[$pointer][1]  = mb_substr($textInsert, -$suffix) . $diffs[$pointer][1];
              $diffs[$pointer][2] += $suffix;
              $textInsert          = mb_substr($textInsert, 0, -$suffix);
              $lengthInsert       -= $suffix;
              $textDelete          = mb_substr($textDelete, 0, -$suffix);
              $lengthDelete       -= $suffix;
            }
          }

          // Delete offending records and add the merged ones.
          if ($countDelete === 0) {
            array_splice($diffs, $pointer - $countInsert, $countInsert, [[ self::INSERT_KEY, $textInsert, $lengthInsert ]]);
          }
          elseif ($countInsert === 0) {
            array_splice($diffs, $pointer - $countDelete, $countDelete, [[ self::DELETE_KEY, $textDelete, $lengthDelete ]]);
          }
          else {
            array_splice($diffs, $pointer - $countDelete - $countInsert, $countDelete + $countInsert, [
              [ self::DELETE_KEY, $textDelete, $lengthDelete ],
              [ self::INSERT_KEY, $textInsert, $lengthInsert ],
            ]);
          }

          $pointer = $pointer - $countDelete - $countInsert + 1;
          if ($countDelete !== 0) {
            ++$pointer;
          }
          if ($countInsert !== 0) {
            ++$pointer;
          }
        }
        // Merge copy with previous copy.
        elseif ($pointer !== 0 && $diffs[$pointer - 1][0] === self::COPY_KEY) {
          $previous = $pointer - 1;
          $diffs[$previous][1] .= $diffs[$pointer][1];
          $diffs[$previous][2] += $diffs[$pointer][2];
          array_splice($diffs, $pointer, 1);
        }
        else {
          ++$pointer;
        }
        $countDelete = $countInsert = $lengthDelete = $lengthInsert = 0;
        $textDelete  = $textInsert  = null;
      }
    }

    if ($diffs[count($diffs) - 1][1] === null) {
      array_pop($diffs);
    }

    // Second pass: look for single transformations surrounded on both sides by copy transformations which can be
    // shifted sideways to eliminate a copy; e.g.:
    //
    // A<ins>BA</ins>C  =>  <ins>AB</ins>AC
    $changes = false;

    // Ignore first and last elements, no need to check those because they have no surrounding transformations.
    $prev    = 0;
    $pointer = 1;
    $next    = 2;
    while ($pointer < count($diffs) - 1) {
      // Check if this transformation is actually surrounded by copy transformations.
      if ($diffs[$prev][0] === self::COPY_KEY && $diffs[$next][0] === self::COPY_KEY) {
        // Shift the transformation over the previous copy transformation.
        if (mb_substr($diffs[$pointer][1], -$diffs[$prev][2]) === $diffs[$prev][1]) {
          $diffs[$pointer][1]  = $diffs[$prev][1] . mb_substr($diffs[$pointer][1], 0, -$diffs[$prev][2]);
          $diffs[$pointer][2] -= $diffs[$prev][2];
          $diffs[$next][1]     = "{$diffs[$prev][1]}{$diffs[$next][1]}";
          $diffs[$next][2]    += $diffs[$prev][2];
          array_splice($diffs, $prev, 1);
          $changes = true;
        }
        // Shift the transformation over the next copy transformation.
        elseif (mb_substr($diffs[$pointer][1], 0, $diffs[$next][2]) === $diffs[$next][1]) {
          $diffs[$prev][1]     = "{$diffs[$prev][1]}{$diffs[$next][1]}";
          $diffs[$prev][2]    += $diffs[$next][2];
          $diffs[$pointer][1]  = mb_substr($diffs[$pointer][1], $diffs[$next][2]) . $diffs[$next][1];
          $diffs[$pointer][2] -= $diffs[$next][2];
          array_splice($diffs, $next, 1);
          $changes = true;
        }
      }
      ++$prev;
      ++$pointer;
      ++$next;
    }

    // If we shifted something, go through all of it again.
    if ($changes === true) {
      return $this->cleanUpDiff($diffs);
    }

    return $diffs;
  }

  /**
   *
   * @param type $text1
   * @param type $text1Length
   * @param type $text2
   * @param type $text2Length
   * @return int
   */
  protected function commonPrefix($text1, $text1Length, $text2, $text2Length) {
    if ($text1 == "" || $text2 == "" || mb_substr($text1, 0, 1) != mb_substr($text2, 0, 1)) {
      return 0;
    }

    $min = $start = 0;
    $max = $mid   = min($text1Length, $text2Length);

    while ($min < $mid) {
      $len = $mid - $start;
      if (mb_substr($text1, $start, $len) == mb_substr($text2, $start, $len)) {
        $start = $min = $mid;
      }
      else {
        $max = $mid;
      }
      $mid = (integer) (($max - $min) / 2) + $min;
    }

    return $mid;
  }

  /**
   *
   * @param type $text1
   * @param type $text1Length
   * @param type $text2
   * @param type $text2Length
   * @return int
   */
  protected function commonSuffix($text1, $text1Length, $text2, $text2Length) {
    if ($text1 == "" || $text2 == "" || mb_substr($text1, -1, 1) != mb_substr($text2, -1, 1)) {
      return 0;
    }

    $min = $end = 0;
    $max = $mid = min($text1Length, $text2Length);

    while ($min < $mid) {
      $len = $mid - $end;
      if (mb_substr($text1, -$mid, $len) == mb_substr($text2, -$mid, $len)) {
        $end = $min = $mid;
      }
      else {
        $max = $mid;
      }
      $mid = (integer) (($max - $min) / 2) + $min;
    }

    return $mid;
  }

  /**
   *
   * @param type $text1
   * @param type $text1Length
   * @param type $text2
   * @param type $text2Length
   * @return type
   */
  protected function compute($text1, $text1Length, $text2, $text2Length, $deadline) {
    // The first text is empty, simple insertion necessary.
    if ($text1 == "") {
      return [[ self::INSERT_KEY, $text2, $text2Length ]];
    }

    // The second text is empty, simple deletion necessary.
    if ($text2 == "") {
      return [[ self::DELETE_KEY, $text1, $text1Length ]];
    }

    // We don't want to repeat ourselfs, therefore we create intermediate variables based on lengths.
    if ($text1Length < $text2Length) {
      $shortText   = $text1;
      $shortLength = $text1Length;
      $longText    = $text2;
      $longLength  = $text2Length;
    }
    else {
      $shortText   = $text2;
      $shortLength = $text2Length;
      $longText    = $text1;
      $longLength  = $text1Length;
    }

    // The shorter text is contained within the longer text.
    if (($i = mb_strpos($longText, $shortText)) !== false) {
      $diff = [
        [ self::INSERT_KEY, mb_substr($longText, 0, $i), $i ],
        [ self::COPY_KEY, $shortText, $shortLength ],
        [ self::INSERT_KEY, mb_substr($longText, ($length = $i + $shortLength)), $longLength - $length ],
      ];

      // Swap insertions with deletions if diff is reversed.
      if ($text2Length < $text1Length) {
        $diff[0][0] = self::DELETE_KEY;
        $diff[2][0] = self::DELETE_KEY;
      }

      return $diff;
    }

    // Single character, cannot be a copy operation because of previous checks.
    if ($shortLength === 1) {
      return [[ self::DELETE_KEY, $text1, $text1Length ], [ self::INSERT_KEY, $text2, $text2Length ]];
    }

    // Don't risk returning a non-optimal patch if we can divide the texts.
    if (($hm = $this->halfMatch($longText, $longLength, $shortText, $shortLength))) {
      return array_merge(
        $this->getDiff($hm[0], $hm[2], $deadline),
        [[ self::COPY_KEY, $hm[4], $hm[5] ]],
        $this->getDiff($hm[1], $hm[3], $deadline)
      );
    }

    return $this->bisect($text1, $text1Length, $text2, $text2Length, $deadline);
  }

  /**
   *
   * @param type $longText
   * @param type $longLength
   * @param type $shortText
   * @param type $shortLength
   * @return null|array
   */
  protected function halfMatch($longText, $longLength, $shortText, $shortLength) {
    // Pointless to continue...
    if ($longLength < 4 || $shortLength * 2 < strlen((string) $longLength)) {
      return;
    }

    // First check if the second quarter is the seed for a half-match.
    $hm1 = $this->halfMatchI($longText, $longLength, $shortText, $shortLength, (integer) ($longLength + 3) / 4);

    // Check again based on the third quarter.
    $hm2 = $this->halfMatchI($longText, $longLength, $shortText, $shortLength, (integer) ($longLength + 1) / 2);

    // Both matched, select the longest.
    if ($hm1 && $hm2) {
      $hm = $hm1[5] > $hm2[5] ? $hm1 : $hm2;
    }
    elseif ($hm1) {
      $hm = $hm1;
    }
    elseif ($hm2) {
      $hm = $hm2;
    }
    else {
      return;
    }

    // Reorder if diff is reversed.
    if ($longLength < $shortLength) {
      return [ $hm[2], $hm[3], $hm[0], $hm[1], $hm[4], $hm[5] ];
    }

    return $hm;
  }

  /**
   *
   * @param type $longText
   * @param type $longLength
   * @param type $shortText
   * @param type $shortLength
   * @param type $i
   * @return type
   */
  protected function halfMatchI($longText, $longLength, $shortText, $shortLength, $i) {
    $seed       = mb_substr($longText, $i, (integer) $longLength / 4);
    $bestCommon = $bestLongA  = $bestLongB  = $bestShortA = $bestShortB = "";
    $j          = mb_strpos($shortText, $seed);

    while ($j !== false) {
      $tmpLongLength  = $longLength  - $i;
      $tmpShortLength = $shortLength - $j;
      $prefixLength   = $this->commonPrefix(mb_substr($longText, $i, $tmpLongLength), $tmpLongLength, mb_substr($shortText, $i, $tmpShortLength), $tmpShortLength);
      $suffixLength   = $this->commonSuffix(mb_substr($longText, 0, $i), $tmpLongLength, mb_substr($shortText, 0, $j), $tmpShortLength);
      if (mb_strlen($bestCommon) < $suffixLength + $prefixLength) {
        $bestCommon = mb_substr($shortText, $j - $suffixLength, $suffixLength) . mb_substr($shortText, $j, $prefixLength);
        $bestLongA  = mb_substr($longText, 0, $i - $suffixLength);
        $bestLongB  = mb_substr($longText, $i + $prefixLength);
        $bestShortA = mb_substr($shortText, 0, $j - $suffixLength);
        $bestShortB = mb_substr($shortText, $j + $prefixLength);
      }
      $j = mb_strpos($shortText, $seed, ++$j);
    }

    if (($bestCommonLength = mb_strlen($bestCommon)) * 2 >= $longLength) {
      return [ $bestLongA, $bestLongB, $bestShortA, $bestShortB, $bestCommon, $bestCommonLength ];
    }
  }

  /**
   *
   * @param type $text1
   * @param type $text1Length
   * @param type $text2
   * @param type $text2Length
   */
  protected function bisect($text1, $text1Length, $text2, $text2Length, $deadline) {
    $maxD             = (integer) ($text1Length + $text2Length + 1) / 2;
    $vOffset          = $maxD;
    $vLength          = 2 * $maxD;
    $v1               = array_fill(0, $vLength, -1);
    $v1[$vOffset + 1] = 0;
    $v2               = $v1;
    $delta            = $text1Length - $text2Length;

    // Front path will collide with reverse path if total character count is odd.
    $even = ($delta % 2 === 0); // true = even | false = odd

    // Offsets for start and end of k loops, prevents mapping of space beyond the grid.
    $k1Start = $k1End = $k2Start = $k2End = 0;

    for ($d = 0; $d < $maxD; ++$d) {
      // Bail out if deadline is reached.
      if (microtime(true) > $deadline) {
        break;
      }

      // Walk the front path one step.
      for ($k1 = -$d + $k1Start; $k1 < $d + 1 - $k1End; $k1 += 2) {
        $k1Offset  = $vOffset + $k1;
        $k1OffsetD = $k1Offset - 1;
        $k1OffsetI = $k1Offset + 1;
        if ($k1 === -$d || ($k1 !== $d && $v1[$k1OffsetD] < $v1[$k1OffsetI])) {
          $x1 = $v1[$k1OffsetI];
        }
        else {
          $x1 = $v1[$k1OffsetD] + 1;
        }
        $y1 = $x1 - $k1;
        while ($x1 < $text1Length && $y1 < $text2Length && mb_substr($text1, $x1, 1) == mb_substr($text2, $y1, 1)) {
          ++$x1;
          ++$y1;
        }
        $v1[$k1Offset] = $x1;

        // Outside the right of the graph.
        if ($x1 > $text1Length) {
          $k1End += 2;
        }
        // Outside the bottom of the graph.
        elseif ($y1 > $text2Length) {
          $k1Start += 2;
        }
        // Additional checks in odd case.
        elseif ($even === false) {
          $k2Offset = $vOffset + $delta - $k1;
          if ($k2Offset >= 0 && $k2Offset < $vLength && $v2[$k2Offset] !== -1) {
            $x2 = $text1Length - $v2[$k2Offset];
            if ($x1 >= $x2) {
              return array_merge(
                $this->getDiff(mb_substr($text1, 0, $x1), mb_substr($text2, 0, $y1), $deadline),
                $this->getDiff(mb_substr($text1, $x1), mb_substr($text2, $y1), $deadline)
              );
            }
          }
        }
      }

      // Walk the back path one step.
      for ($k2 = -$d + $k2Start; $k2 < $d + 1 - $k2End; $k2 += 2) {
        $k2Offset  = $vOffset + $k2;
        $k2OffsetD = $k2Offset - 1;
        $k2OffsetI = $k2Offset + 1;
        if ($k2 === -$d || ($k2 !== $d && $v2[$k2OffsetD] < $v2[$k2OffsetI])) {
          $x2 = $v2[$k2OffsetI];
        }
        else {
          $x2 = $v2[$k2OffsetD] + 1;
        }
        $y2 = $x2 - $k2;
        while ($x2 < $text1Length && $y2 < $text2Length && mb_substr($text1, -$x2 - 1, 1) == mb_substr($text2, -$y2 - 1, 1)) {
          ++$x2;
          ++$y2;
        }
        $v2[$k2Offset] = $x2;

        // Outside the right of the graph.
        if ($x2 > $text1Length) {
          $k2End += 2;
        }
        // Outside the bottom of the graph.
        elseif ($y2 > $text2Length) {
          $k2Start += 2;
        }
        // Additional checks in even case.
        elseif ($even === true) {
          $k1Offset = $vOffset + $delta - $k2;
          if ($k1Offset >= 0 && $k1Offset < $vLength && $v1[$k1Offset] !== -1) {
            $x1 = $v1[$k1Offset];
            $y1 = $vOffset + $x1 - $k1Offset;
            $x2 = $text1Length - $x2;
            if ($x1 >= $x2) {
              return array_merge(
                $this->getDiff(mb_substr($text1, 0, $x1), mb_substr($text2, 0, $y1), $deadline),
                $this->getDiff(mb_substr($text1, $x1), mb_substr($text2, $y1), $deadline)
              );
            }
          }
        }
      }
    }

    // Diff took too long and hit the deadline or number of diffs equals number of characters, no commonality at all.
    return [
      [ self::DELETE_KEY, $text1, $text1Length ],
      [ self::INSERT_KEY, $text2, $text2Length ],
    ];
  }

}
