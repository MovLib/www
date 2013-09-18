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
namespace MovLib\Test\Utility;

use \MovLib\Utility\String;
use \PHPUnit_Framework_TestCase;

/**
 * Tests for the various methods of the string utility class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class StringTest extends PHPUnit_Framework_TestCase {

  public function testCheckPlain() {
    $this->assertEquals(
      "test&quot;string&quot;with&lt;html&gt;embedded&lt;script&gt;reserved&apos;&apos;tags&apos;&apos;",
      String::checkPlain("test\"string\"with<html>embedded<script>reserved''tags''")
    );
  }

  public function testCheckUrl() {
    $this->assertEquals(
      "my test&period;php&quest;name&equals;st&aring;le&amp;car&equals;aston-martin",
      String::checkUrl("my test.php?name=ståle&car=aston-martin")
    );
  }

  public function testConvertToRoute() {
    $this->assertEquals(
      "☆-my-route-teststring-with-some-unicode-★",
      String::convertToRoute("☆ My Route TestString with some Unicode ★")
    );
  }

  public function testPlaceholder() {
    $this->assertEquals(
      "<em class='placeholder'>Placeholder &lt;Test&gt;</em>",
      String::placeholder("Placeholder <Test>")
    );
  }

  public function testCollapseWhitspace() {
    $this->assertEquals(
      "This string should not have any linefeeds, tabs, nor multiple whitespaces.",
      String::collapseWhitespace("This\nstring\rshould\r\nnot\nhave\rany\r\nlinefeeds,\ttabs,          nor multiple whitespaces.")
    );
  }


  // ------------------------------------------------------------------------------------------------------------------- Normalize Line Feeds


  public static function normalizeLineFeedsTestProvider() {
    return [
      [ "\n", "\n" ],
      [ "\n", "\r" ],
      [ "\n", "\r\n" ],
      [ "mov\nlib", "mov\nlib" ],
      [ "mov\nlib", "mov\rlib" ],
      [ "mov\nlib", "mov\r\nlib" ],
    ];
  }

  /**
   * @dataProvider normalizeLineFeedsTestProvider
   */
  public function testNormalizeLineFeeds($expected, $testString) {
    $this->assertEquals($expected, String::normalizeLineFeeds($testString));
  }

}
