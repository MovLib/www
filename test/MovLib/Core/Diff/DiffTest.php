<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 **
 * Copyright © 2006 Google Inc.
 * Copyright © 2013 Daniil Skrobov <yetanotherape@gmail.com>
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
 * Please see the covered class ({@see \MovLib\Core\Diff\Diff}) for more information!
 *
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


  // ------------------------------------------------------------------------------------------------------------------- Diff::bisect


  public function dataProviderBisect() {
    return [
      [ [
        [ Diff::DELETE_KEY, "c", 1 ],
        [ Diff::INSERT_KEY, "m", 1 ],
        [ Diff::COPY_KEY, "a", 1 ],
        [ Diff::DELETE_KEY, "t", 1 ],
        [ Diff::INSERT_KEY, "p", 1 ],
      ], "cat", "map", (float) PHP_INT_MAX ],
      [ [
        [ Diff::DELETE_KEY, "cat", 3 ],
        [ Diff::INSERT_KEY, "map", 3 ],
      ], "cat", "map", 0.0 ]
    ];
  }

  /**
   * @covers Diff::bisect
   * @dataProvider dataProviderBisect
   * @param array $expected
   * @param string $text1
   * @param string $text2
   * @param float $deadline
   */
  public function testBisect($expected, $text1, $text2, $deadline) {
    $this->assertEquals($expected, $this->invoke($this->diff, "bisect", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2), $deadline ]));
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


  // ------------------------------------------------------------------------------------------------------------------- Diff::diff


  public function dataProviderDiff() {
    return [
      [ [], "", "" ],
      [ [[ Diff::COPY_KEY, "abc", 3 ]], "abc", "abc" ], // This would return an empty array if we'd called getDiff()!
      [ [
        [ Diff::COPY_KEY, "0", 1 ],
        [ Diff::INSERT_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "12", 2 ],
        [ Diff::INSERT_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "0", 1 ],
        [ Diff::INSERT_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "34", 2 ],
        [ Diff::INSERT_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "0", 1 ],
      ], "0120340", "0X12X0X34X0" ],
      [ [
        [ Diff::COPY_KEY, "0", 1 ],
        [ Diff::DELETE_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "12", 2 ],
        [ Diff::DELETE_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "0", 1 ],
        [ Diff::DELETE_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "34", 2 ],
        [ Diff::DELETE_KEY, "X", 1 ],
        [ Diff::COPY_KEY, "0", 1 ],
      ], "0X12X0X34X0", "0120340" ],
      [ [
        [ Diff::DELETE_KEY, "Apple", 5 ],
        [ Diff::INSERT_KEY, "Banana", 6 ],
        [ Diff::COPY_KEY, "s are a", 7 ],
        [ Diff::INSERT_KEY, "lso", 3 ],
        [ Diff::COPY_KEY, " fruit.", 7 ],
      ], "Apples are a fruit.", "Bananas are also fruit." ],
      [ [
        [ Diff::DELETE_KEY, "a", 1 ],
        [ Diff::INSERT_KEY, "ڀ", 1 ],
        [ Diff::COPY_KEY, "x", 1 ],
        [ Diff::DELETE_KEY, "\t", 1 ],
        [ Diff::INSERT_KEY, "\x00", 1 ],
      ], "ax\t", "ڀx\x00" ],
      [ [
        [ Diff::DELETE_KEY, "1", 1 ],
        [ Diff::COPY_KEY, "a", 1 ],
        [ Diff::DELETE_KEY, "y", 1 ],
        [ Diff::COPY_KEY, "b", 1 ],
        [ Diff::DELETE_KEY, "2", 1 ],
        [ Diff::INSERT_KEY, "xab", 3 ],
      ], "1ayb2", "abxab" ],
      [ [
        [ Diff::INSERT_KEY, "xaxcx", 5 ],
        [ Diff::COPY_KEY, "abc", 3 ],
        [ Diff::DELETE_KEY, "y", 1 ],
      ], "abcy", "xaxcxabc" ],
      [ [
        [ Diff::DELETE_KEY, "ABCD", 4 ],
        [ Diff::COPY_KEY, "a", 1 ],
        [ Diff::DELETE_KEY, "=", 1 ],
        [ Diff::INSERT_KEY, "-", 1 ],
        [ Diff::COPY_KEY, "bcd", 3 ],
        [ Diff::DELETE_KEY, "=", 1 ],
        [ Diff::INSERT_KEY, "-", 1 ],
        [ Diff::COPY_KEY, "efghijklmnopqrs", 15 ],
        [ Diff::DELETE_KEY, "EFGHIJKLMNOefg", 14 ],
      ], "ABCDa=bcd=efghijklmnopqrsEFGHIJKLMNOefg", "a-bcd-efghijklmnopqrs" ],
      [ [
        [ Diff::INSERT_KEY, " ", 1 ],
        [ Diff::COPY_KEY, "a", 1 ],
        [ Diff::INSERT_KEY, "nd", 2 ],
        [ Diff::COPY_KEY, " [[Pennsylvania]]", 17 ],
        [ Diff::DELETE_KEY, " and [[New", 10 ],
      ], "a [[Pennsylvania]] and [[New", " and [[Pennsylvania]]" ],
    ];
  }

  /**
   * @covers Diff::diff
   * @dataProvider dataProviderDiff
   * @param array $expected
   * @param string $text1
   * @param string $text2
   */
  public function testDiff($expected, $text1, $text2) {
    $this->assertEquals($expected, $this->invoke($this->diff, "diff", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2), (float) PHP_INT_MAX ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::getDiff


  /**
   * @covers Diff::getDiff
   */
  public function testGetDiff() {
    $this->assertEquals([], $this->diff->getDiff("foo💩", "foo💩"));
  }

  /**
   * @see ::dataProviderDiff
   */
  public function dataProviderGetDiffDiff() {
    $data = $this->dataProviderDiff();
    $data[1][0] = [];
    return $data;
  }

  /**
   * @see DiffTest::testDiff
   * @covers Diff::getDiff
   * @dataProvider dataProviderGetDiffDiff
   * @param array $expected
   * @param string $text1
   * @param string $text2
   */
  public function testGetDiffDiff($expected, $text1, $text2) {
    $this->assertEquals($expected, $this->diff->getDiff($text1, $text2));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::getDiffPatch




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
