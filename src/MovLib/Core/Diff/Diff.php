<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2006 Google Inc.
 * Copyright © 2013 Daniil Skrobov <yetanotherape@gmail.com>
 *
 * Copyright © 2011 Raymond Hill {@link http://raymondhill.net/}
 * Copyright © 2013 Rob Crowe {@link http://cogpowered.com/}
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
 * We couldn't find a good diff software that works on character basis and supports 4-byte UTF-8 characters. Therefore
 * we were forced to create our own implementation that is fast and has support for our requirements.
 *
 * <b>NOTE</b><br>
 * This class incorporates code from the following projects:
 *
 * <ul>
 *   <li>{@link https://github.com/yetanotherape/diff-match-patch}</li>
 *   <li>{@link https://github.com/cogpowered/FineDiff/}</li>
 * </ul>
 *
 * The first project is licensed under the Apache License 2.0 which is compatible with the AGPLv3 and latter is under
 * the MIT license which is compatible with the AGPLv3 license as well. We checked this to our best knowledge and hope
 * that this is actually true. We are no lawyers and incorporated the code of those projects in the hope to benefit the
 * open source world with our approach to the problem.
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
   * <b>NOTE</b><br>
   * We need a longer deadline because of UTF-8, it simply takes some time.
   *
   * @var float
   */
  const DEADLINE = 2.0;

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

  /**
   * The character that is used as separator within insert transformations.
   *
   * The separator is important for correct extraction of the length of an insert transformation. We can't be certain
   * that the first character of an insert operation isn't a numeric one. The actual separator character doesn't matter,
   * it simply has to be known.
   *
   * @var string
   */
  const INSERT_SEPARATOR = ":";


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Apply patch to a string and recreate a previous version of this string.
   *
   * @link https://github.com/cogpowered/FineDiff/
   *   This code is based on the FineDiff project.
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


  // ------------------------------------------------------------------------------------------------------------------- NEW Public Methods


  /**
   * Get the differences between <var>$new</var> and <var>$old</var>.
   *
   * @param string $new
   *   The new text.
   * @param string $old
   *   The old text.
   * @return array
   *   The differences between <var>$new</var> and <var>$old</var>.
   */
  public function getDiff($new, $old) {
    // No need to call the diff method if both texts are equal. Note that this will never happen if we're called as part
    // of our revisioning system, this is simply because the created date of each revision always changes.
    if ($new === $old) {
      return [];
    }

    // Perform the actual diff, this method is only a public wrapper for the actual diff method that has an extended
    // signature. See the method's documentation for more info on this.
    return $this->diff($new, mb_strlen($new), $old, mb_strlen($old), microtime(true) + self::DEADLINE);
  }

  /**
   * Get a human readable diff patch.
   *
   * <b>NOTE</b><br>
   * The patch is optimized for space and compatible with FineDiff.
   *
   * @param array $diffs
   *   The diffs to get human readable patch for.
   * @return string
   *   The human readable diff patch.
   */
  public function getDiffPatch(array $diffs) {
    $patch = null;
    $c     = count($diffs);
    for ($i = 0; $i < $c; ++$i) {
      if ($diffs[$i][0] === self::INSERT_KEY) {
        $patch .= self::INSERT . ($diffs[$i][2] === 1 ? null : $diffs[$i][2]) . self::INSERT_SEPARATOR . $diffs[$i][1];
      }
      else {
        if ($diffs[$i][0] === self::COPY_KEY) {
          $patch .= self::COPY;
        }
        else {
          $patch .= self::DELETE;
        }
        $patch .= $diffs[$i][2] === 1 ? null : $diffs[$i][2];
      }
    }
    return $patch;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Find the <i>middle snake</i> of a diff, split the problem in half and return the recursively constructed diff.
   *
   * @link http://citeseerx.ist.psu.edu/viewdoc/summary?doi=10.1.1.4.6927
   *   Eugene W. Myers: "An O(ND) Difference Algorithm and Its Variations" (1986)
   * @param string $text1
   *   The first text to check.
   * @param integer $text1Length
   *   The length of the first text (be sure to use a multi-byte aware function).
   * @param string $text2
   *   The second text to check.
   * @param integer $text2Length
   *   The length of the second text (be sure to use a multi-byte aware function).
   * @param float $deadline
   *   The microtime at which compilation should abort.
   * @return array
   *   Array containing the differences between <var>$text1</var> and <var>$text2</var>.
   */
  protected function bisect($text1, $text1Length, $text2, $text2Length, $deadline) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ 1, 2 ] as $assert) {
      $assertText   = ${"text{$assert}"};
      $assertLength = ${"text{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    assert(is_float($deadline), "Deadline must be of type float.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $dMax             = (integer) (($text1Length + $text2Length + 1) / 2);
    $vOffset          = $dMax;
    $vLength          = $dMax * 2;
    $v1               = array_fill(0, $vLength, -1);
    $v1[$vOffset + 1] = 0;
    $v2               = $v1;
    $delta            = $text1Length - $text2Length;

    // Front path will collide with reverse path if total character count is odd.
    $even = ($delta % 2 === 0); // true = even | false = odd

    // Offsets for start and end of k loops, prevents mapping of space beyond the grid.
    $k1Start = $k1End = $k2Start = $k2End = 0;

    // Start of greedy LCS/SES algorithm, see page 6 of the original paper.
    for ($d = 0; $d < $dMax; ++$d) {
      // Bail out if deadline is reached.
      if (microtime(true) > $deadline) {
        break;
      }

      // Walk the front path in steps of 2.
      for ($k1 = -$d + $k1Start; $k1 < $d + 1 - $k1End; $k1 += 2) {
        $k1Offset          = $vOffset + $k1;
        $k1OffsetDecrement = $k1Offset - 1;
        $k1OffsetIncrement = $k1Offset + 1;

        if ($k1 === -$d || ($k1 !== $d && $v1[$k1OffsetDecrement] < $v1[$k1OffsetIncrement])) {
          $x1 = $v1[$k1OffsetIncrement];
        }
        else {
          $x1 = $v1[$k1OffsetDecrement] + 1;
        }

        $y1 = $x1 - $k1;
        while ($x1 < $text1Length && $y1 < $text2Length && mb_substr($text1, $x1, 1) === mb_substr($text2, $y1, 1)) {
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

            // Check if we have an overlap.
            if ($x1 >= $x2) {
              return array_merge(
                $this->diff(mb_substr($text1, 0, $x1), $x1, mb_substr($text2, 0, $y1), $y1, $deadline),
                $this->diff(mb_substr($text1, $x1), $text1Length - $x1, mb_substr($text2, $y1), $text2Length - $y1, $deadline)
              );
            }
          }
        }
      }

      // Walk the back path one step.
      for ($k2 = -$d + $k2Start; $k2 < $d + 1 - $k2End; $k2 += 2) {
        $k2Offset          = $vOffset + $k2;
        $k2OffsetDecrement = $k2Offset - 1;
        $k2OffsetIncrement = $k2Offset + 1;

        if ($k2 === -$d || ($k2 !== $d && $v2[$k2OffsetDecrement] < $v2[$k2OffsetIncrement])) {
          $x2 = $v2[$k2OffsetIncrement];
        }
        else {
          $x2 = $v2[$k2OffsetDecrement] + 1;
        }

        $y2 = $x2 - $k2;
        while ($x2 < $text1Length && $y2 < $text2Length && mb_substr($text1, -$x2 - 1, 1) === mb_substr($text2, -$y2 - 1, 1)) {
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

            // Check if we have an overlap.
            if ($x1 >= $x2) {
              return array_merge(
                $this->diff(mb_substr($text1, 0, $x1), $x1, mb_substr($text2, 0, $y1), $y1, $deadline),
                $this->diff(mb_substr($text1, $x1), $text1Length - $x1, mb_substr($text2, $y1), $text2Length - $y1, $deadline)
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

  /**
   * Get the length of the longest common prefix of <var>$text1</var> and <var>$text2</var>.
   *
   * @link http://neil.fraser.name/news/2007/10/09/ Performance Analysis
   * @see Diff::commonSuffix()
   * @param string $text1
   *   The first text to check.
   * @param integer $text1Length
   *   The length of the first text (be sure to use a multi-byte aware function).
   * @param string $text2
   *   The second text to check.
   * @param integer $text2Length
   *   The length of the second text (be sure to use a multi-byte aware function).
   * @return integer
   *   The length of the longest common prefix of <var>$text1</var> and <var>$text2</var>.
   */
  protected function commonPrefix($text1, $text1Length, $text2, $text2Length) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ 1, 2 ] as $assert) {
      $assertText   = ${"text{$assert}"};
      $assertLength = ${"text{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Directly take care of easy cases that have nothing in common for sure.
    if ($text1Length === 0 || $text2Length === 0 || mb_substr($text1, 0, 1) !== mb_substr($text2, 0, 1)) {
      return 0;
    }

    // Prepare variables for binary search, the shorter string is our upper limit during the following loop.
    $min = $stx = 0;
    $max = $mid = $len = min($text1Length, $text2Length);

    // Binary Search: https://en.wikipedia.org/wiki/Binary_search_algorithm
    while ($min < $mid) {
      if (mb_substr($text1, $stx, $len) === mb_substr($text2, $stx, $len)) {
        $stx = $min = $mid;
      }
      else {
        $max = $mid;
      }
      $mid = (integer) ((($max - $min) / 2) + $min);
      $len = $mid - $stx;
    }

    return $mid;
  }

  /**
   * Get the length of the longest common suffix of <var>$text1</var> and <var>$text2</var>.
   *
   * @link http://neil.fraser.name/news/2007/10/09/ Performance Analysis
   * @see Diff::commonPrefix()
   * @param string $text1
   *   The first text to check.
   * @param integer $text1Length
   *   The length of the first text (be sure to use a multi-byte aware function).
   * @param string $text2
   *   The second text to check.
   * @param integer $text2Length
   *   The length of the second text (be sure to use a multi-byte aware function).
   * @return integer
   *   The length of the longest common suffix of <var>$text1</var> and <var>$text2</var>.
   */
  protected function commonSuffix($text1, $text1Length, $text2, $text2Length) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ 1, 2 ] as $assert) {
      $assertText   = ${"text{$assert}"};
      $assertLength = ${"text{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Directly take care of easy cases that have nothing in common for sure.
    if ($text1Length === 0 || $text2Length === 0 || mb_substr($text1, -1, 1) !== mb_substr($text2, -1, 1)) {
      return 0;
    }

    // Prepare variables for binary search, the shorter string is our upper limit during the following loop.
    $min = $end = 0;
    $max = $mid = $len = min($text1Length, $text2Length);

    // Binary Search: https://en.wikipedia.org/wiki/Binary_search_algorithm
    while ($min < $mid) {
      if (mb_substr($text1, -$mid, $len) === mb_substr($text2, -$mid, $len)) {
        $end = $min = $mid;
      }
      else {
        $max = $mid;
      }
      $mid = (integer) ((($max - $min) / 2) + $min);
      $len = $mid - $end;
    }

    return $mid;
  }

  /**
   * Compute differences between <var>$text1</var> and <var>$text2</var> that have no common pre- nor suffix.
   *
   * @param string $text1
   *   The first text to check.
   * @param integer $text1Length
   *   The length of the first text (be sure to use a multi-byte aware function).
   * @param string $text2
   *   The second text to check.
   * @param integer $text2Length
   *   The length of the second text (be sure to use a multi-byte aware function).
   * @param float $deadline
   *   The microtime at which compilation should abort.
   * @return array
   *   Array containing the differences between <var>$text1</var> and <var>$text2</var>.
   */
  protected function compute($text1, $text1Length, $text2, $text2Length, $deadline) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ 1, 2 ] as $assert) {
      $assertText   = ${"text{$assert}"};
      $assertLength = ${"text{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    assert(is_float($deadline), "Deadline must be of type float.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Both texts are equal, simply return.
    if ($text1 === $text2) {
      return [[ self::COPY_KEY, $text1, $text1Length ]];
    }

    // The first text is empty, simple insertion necessary.
    if ($text1Length === 0) {
      return [[ self::INSERT_KEY, $text2, $text2Length ]];
    }

    // The second text is empty, simple deletion necessary.
    if ($text2Length === 0) {
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
      // Reorder if diff is reversed.
      if ($text1Length < $text2Length) {
        $hm = [ $hm[2], $hm[3], $hm[0], $hm[1], $hm[4], $hm[5] ];
      }

      // Compute differences of left and right hand side of the copy operation of the common middle characters.
      $diffs   = $this->diff($hm[0], mb_strlen($hm[0]), $hm[2], mb_strlen($hm[2]), $deadline);
      $diffs[] = [ self::COPY_KEY, $hm[4], $hm[5] ];
      return array_merge($diffs, $this->diff($hm[1], mb_strlen($hm[1]), $hm[3], mb_strlen($hm[3]), $deadline));
    }

    return $this->bisect($text1, $text1Length, $text2, $text2Length, $deadline);
  }

  /**
   * Find the differences between <var>$text1</var> and <var>$text2</var>.
   *
   * @param string $text1
   *   The first text to compare.
   * @param type $text1Length
   *   Used internally for recursive calls.
   * @param string $text2
   *   The second text to compare.
   * @param type $text2Length
   *   Used internally for recursive calls.
   * @param float $deadline
   *   Used internally for recursive calls.
   * @return array
   *   Array containing the differences between <var>$text1</var> and <var>$text2</var>.
   */
  protected function diff($text1, $text1Length, $text2, $text2Length, $deadline) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ 1, 2 ] as $assert) {
      $assertText   = ${"text{$assert}"};
      $assertLength = ${"text{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    assert(is_float($deadline), "Deadline must be of type float.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Check for equality.
    if ($text1 === $text2) {
      // We always return an array, this makes sure that we don't generate errors together with all other methods that
      // expect an array to work with. Note that the final generated patch is still NULL.
      if ($text1 === "") {
        return [];
      }

      // Simply copy the complete text and we're done. Note, we don't know if we're called in recursion or not, that's
      // why the public wrapper has to take care of any initial equality. We have to copy, because there might be other
      // transformations surrounding us.
      return [[ self::COPY_KEY, $text1, $text1Length ]];
    }

    // Compute common prefix.
    if (($prefixLength = $this->commonPrefix($text1, $text1Length, $text2, $text2Length)) === 0) {
      $prefix = null;
    }
    else {
      $prefix       = mb_substr($text1, 0, $prefixLength);
      $text1Length -= $prefixLength;
      $text2Length -= $prefixLength;
      $text1        = mb_substr($text1, $prefixLength);
      $text2        = mb_substr($text2, $prefixLength);
    }

    // Compute common suffix.
    if (($suffixLength = $this->commonSuffix($text1, $text1Length, $text2, $text2Length)) === 0) {
      $suffix = null;
    }
    else {
      $suffix       = mb_substr($text1, -$suffixLength);
      $text1Length -= $suffixLength;
      $text2Length -= $suffixLength;
      $text1        = mb_substr($text1, 0, $text1Length);
      $text2        = mb_substr($text2, 0, $text2Length);
    }

    // Compute differences in middle block.
    $diffs = $this->compute($text1, $text1Length, $text2, $text2Length, $deadline);

    // Restore common prefix.
    if ($prefix) {
      array_unshift($diffs, [ self::COPY_KEY, $prefix, $prefixLength ]);
    }

    // Append common suffix.
    if ($suffix) {
      $diffs[] = [ self::COPY_KEY, $suffix, $suffixLength ];
    }
return $diffs;
    // Clean the differences by merging as many operations as possible.
    return $this->merge($diffs);
  }

  /**
   * Check if the two texts share a substrig which is at least half the length of the longer text.
   *
   * <b>NOTE</b><br>
   * This method isn't inlined for unit test reasons.
   *
   * @param string $longText
   *   The longer text.
   * @param integer $longLength
   *   The length of the longer text.
   * @param string $shortText
   *   The shorter text.
   * @param integer $shortLength
   *   The length of the shorter text.
   * @return null|array
   *   An array containing six elements in the form:
   *   <ul>
   *     <li><code>0</code>: The prefix of the longer text.</li>
   *     <li><code>1</code>: The suffix of the longer text.</li>
   *     <li><code>2</code>: The prefix of the shorter text.</li>
   *     <li><code>3</code>: The suffix of the shorter text.</li>
   *     <li><code>4</code>: The common middle text.</li>
   *     <li><code>5</code>: The length of the common middle text.</li>
   *   </ul>
   *   If no shared substring was found <code>NULL</code> is returned.
   */
  protected function halfMatch($longText, $longLength, $shortText, $shortLength) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "long", "short" ] as $assert) {
      $assertText   = ${"{$assert}Text"};
      $assertLength = ${"{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    assert($longLength >= $shortLength, "The short text must be shorter than the long text (or at least equal).");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Pointless to continue...
    if ($longLength < 4 || $shortLength * 2 < strlen($longLength)) {
      return;
    }

    // First check if the second quarter is the seed for a half-match.
    $hm1 = $this->halfMatchIndex($longText, $longLength, $shortText, $shortLength, (integer) (($longLength + 3) / 4));

    // Check again based on the third quarter.
    $hm2 = $this->halfMatchIndex($longText, $longLength, $shortText, $shortLength, (integer) (($longLength + 1) / 2));

    // Both matched, select the longest.
    if ($hm1 && $hm2) {
      return $hm1[5] > $hm2[5] ? $hm1 : $hm2;
    }
    elseif ($hm1) {
      return $hm1;
    }
    elseif ($hm2) {
      return $hm2;
    }
  }

  /**
   * Check if a substring of the shorter text exists within the longer text such that the substring is at least half the
   * length of the longer text.
   *
   * @param string $longText
   *   The longer text.
   * @param integer $longLength
   *   The length of the longer text.
   * @param string $shortText
   *   The shorter text.
   * @param integer $shortLength
   *   The length of the shorter text.
   * @param integer $i
   *   Start index of quarter length substring within the longer text.
   * @return null|array
   *   An array containing six elements in the form:
   *   <ul>
   *     <li><code>0</code>: The prefix of the longer text.</li>
   *     <li><code>1</code>: The suffix of the longer text.</li>
   *     <li><code>2</code>: The prefix of the shorter text.</li>
   *     <li><code>3</code>: The suffix of the shorter text.</li>
   *     <li><code>4</code>: The common middle text.</li>
   *     <li><code>5</code>: The length of the common middle text.</li>
   *   </ul>
   *   If no shared substring was found <code>NULL</code> is returned.
   */
  protected function halfMatchIndex($longText, $longLength, $shortText, $shortLength, $i) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "long", "short" ] as $assert) {
      $assertText   = ${"{$assert}Text"};
      $assertLength = ${"{$assert}Length"};
      assert(is_string($assertText), "Text {$assert} must be of type string.");
      assert(is_int($assertLength), "Text {$assert} length must be of type integer.");
      assert(mb_strlen($assertText) === $assertLength, "Text {$assert} length must be correct (multi-byte).");
    }
    assert($longLength >= $shortLength, "The short text must be shorter than the long text (or at least equal).");
    assert(is_int($i), "Index must be of type integer.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Extract a quarter from the long text, starting at the desired index.
    $seed = mb_substr($longText, $i, (integer) ($longLength / 4));

    // Prepare variables for half match search.
    $bestCommon = $bestLongA  = $bestLongB  = $bestShortA = $bestShortB = "";

    // Check if the extracted seed is contained in the short text, if not continue search for a match.
    $j = mb_strpos($shortText, $seed);
    while ($j !== false) {
      $prefixLength = $this->commonPrefix(mb_substr($longText, $i), $longLength - $i, mb_substr($shortText, $j), $shortLength - $j);
      $suffixLength = $this->commonSuffix(mb_substr($longText, 0, $i), $i, mb_substr($shortText, 0, $j), $j);

      if (mb_strlen($bestCommon) < $suffixLength + $prefixLength) {
        $bestLongA  = mb_substr($longText, 0, $i - $suffixLength);
        $bestLongB  = mb_substr($longText, $i + $prefixLength);

        $bestShortA = mb_substr($shortText, 0, $j - $suffixLength);
        $bestShortB = mb_substr($shortText, $j + $prefixLength);

        $bestCommon = mb_substr($shortText, $j - $suffixLength, $suffixLength) . mb_substr($shortText, $j, $prefixLength);
      }

      $j = mb_strpos($shortText, $seed, $j + 1);
    }

    if (($bestCommonLength = mb_strlen($bestCommon)) * 2 >= $longLength) {
      return [ $bestLongA, $bestLongB, $bestShortA, $bestShortB, $bestCommon, $bestCommonLength ];
    }
  }

  /**
   *
   * @param array $diffs
   * @return array
   */
  protected function merge(array $diffs) {
    // Why are we adding an empty copy operation at this point?
    $diffs[] = [ self::COPY_KEY, "", 0 ];

    // Declare variables for merge optimization.
    $pointer    = $countDelete = $countInsert = $lengthDelete = $lengthInsert = 0;
    $textDelete = $textInsert  = null;

    // Note that the diffs count changes while we're iterating over it, therefore we have to recount it all the time.
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
        // Advance the pointer and go to the next record.
        else {
          ++$pointer;
        }

        // Reset all variables to their defaults, except the pointer of course.
        $countDelete = $countInsert = $lengthDelete = $lengthInsert = 0;
        $textDelete  = $textInsert  = null;
      }
    }

    // Why are we removing the empty copy operation?
    if ($diffs[count($diffs) - 1][1] === "") {
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
      return $this->merge($diffs);
    }

    return $diffs;
  }

}
