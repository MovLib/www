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

use \MovLib\Core\Diff\Diff;

/**
 * @coversDefaultClass \MovLib\Core\Diff\Diff
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DiffTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * @var \MovLib\Core\Diff\Diff
   */
  protected $diff;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->diff = new Diff();
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::commonPrefix


  public function dataProviderCommonPrefix() {
    return [
      [ 0, "abc", "xyz" ],
      [ 4, "1234abcdef", "1234xyz" ],
      [ 4, "1234", "1234xyz" ],
      [ 3, "FOO💩FOO", "FOOyFOO" ],  // 4-byte UTF-8 as separator
      [ 1, "💩FOO", "💩BAR" ],        // 4-byte UTF-8 in common
    ];
  }

  /**
   * @covers Diff::commonPrefix
   * @dataProvider dataProviderCommonPrefix
   * @param integer $commonPrefixLength
   * @param string $text1
   * @param string $text2
   */
  public function testCommonPrefix($commonPrefixLength, $text1, $text2) {
    $this->assertEquals($commonPrefixLength, $this->invoke($this->diff, "commonPrefix", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2) ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::commonSuffix


  public function dataProviderCommonSuffix() {
    return [
      [ 0, "abc", "xyz" ],
      [ 4, "abcdef1234", "xyz1234" ],
      [ 4, "1234", "xyz1234" ],
      [ 3, "FOO💩FOO", "FOOyFOO" ],  // 4-byte UTF-8 as separator
      [ 1, "FOO💩", "BAR💩" ],        // 4-byte UTF-8 in common
    ];
  }

  /**
   * @covers Diff::commonSuffix
   * @dataProvider dataProviderCommonSuffix
   * @param integer $commonSuffixLength
   * @param string $text1
   * @param string $text2
   */
  public function testCommonSuffix($commonSuffixLength, $text1, $text2) {
    $this->assertEquals($commonSuffixLength, $this->invoke($this->diff, "commonSuffix", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2) ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::halfMatch


  public function dataProviderHalfMatch() {
    return [
      [ null, "1234567890", "abcdef" ],
      [ null, "12345", "23" ],
      [ [ "12", "90", "a", "z", "345678", 6 ], "1234567890", "a345678z" ],
      [ [ "1234", "0", "abc", "z", "56789", 5 ], "1234567890", "abc56789z" ],
      [ [ "1", "7890", "a", "xyz", "23456", 5 ], "1234567890", "a23456xyz" ],
      [ [ "12123", "123121", "a", "z", "1234123451234", 13 ], "121231234123451234123121", "a1234123451234z" ],
      [ [ "", "-=-=-=-=-=", "x", "", "x-=-=-=-=-=-=-=", 15 ], "x-=-=-=-=-=-=-=-=-=-=-=-=", "xx-=-=-=-=-=-=-=" ],
      [ [ "-=-=-=-=-=", "", "", "y", "-=-=-=-=-=-=-=y", 15 ], "-=-=-=-=-=-=-=-=-=-=-=-=y", "-=-=-=-=-=-=-=yy" ],
      [ [ "qHillo", "w", "x", "Hulloy", "HelloHe", 7 ], "qHilloHelloHew", "xHelloHeHulloy" ],
      [ [ "💩12", "90💩", "💩a", "z💩", "345678", 6 ], "💩1234567890💩", "💩a345678z💩" ], // 4-byte UTF-8 character
    ];
  }

  /**
   * @covers Diff::halfMatch
   * @dataProvider dataProviderHalfMatch
   * @param mixed $expected
   * @param string $longText
   * @param string $shortText
   */
  public function testHalfMatch($expected, $longText, $shortText) {
    $this->assertEquals($expected, $this->invoke($this->diff, "halfMatch", [ $longText, mb_strlen($longText), $shortText, mb_strlen($shortText) ]));
  }

}
