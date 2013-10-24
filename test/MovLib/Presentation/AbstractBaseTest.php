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
namespace MovLib\Presentation;

/**
 * @coversDefaultClass \MovLib\Presentation\AbstractBase
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractBaseTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\AbstractBase */
  private $abstractPage;

  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  function dataProviderFormatBytes() {
    return [
      [ 1, "B", 1 ],
      [ 10, "B", 10 ],
      [ 100, "B", 100 ],
      [ 1, "kB", 1000 ],
      [ 10, "kB", 10000 ],
      [ 100, "kB", 100000 ],
      [ 1, "MB", 1000000 ],
      [ 10, "MB", 10000000 ],
      [ 100, "MB", 100000000 ],
      [ 1, "GB", 1000000000 ],
      [ 10, "GB", 10000000000 ],
      [ 100, "GB", 100000000000 ],
      [ 1, "TB", 1000000000000 ],
      [ 10, "TB", 10000000000000 ],
      [ 100, "TB", 100000000000000 ],
      [ 1000, "TB", 1000000000000000 ],
    ];
  }

  function dataProviderNormalizeLineFeeds() {
    return [
      [ "\n", "\n" ],
      [ "\n", "\r" ],
      [ "\n", "\r\n" ],
      [ "mov\nlib", "mov\nlib" ],
      [ "mov\nlib", "mov\rlib" ],
      [ "mov\nlib", "mov\r\nlib" ],
    ];
  }

  // ------------------------------------------------------------------------------------------------------------------- Fixtures And Helpers



  protected function setUp() {
    $this->abstractPage = $this->getMockForAbstractClass("\\MovLib\\Presentation\\AbstractBase");
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::a
   */
  public function testAnchor() {
    $_SERVER["PATH_INFO"] = "/phpunit";
    $this->assertEquals("<a href='/route' title='PHPUnit'>linktext</a>", $this->invoke($this->abstractPage, "a", [ "/route", "linktext", [ "title" => "PHPUnit" ] ]));
  }

  /**
   * @covers ::a
   */
  public function testAnchorPathInfo() {
    $_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"] = "/route";
    $this->assertEquals("<a href='#' class='active'>linktext</a>", $this->invoke($this->abstractPage, "a", [ "/route", "linktext", [ "title" => "PHPUnit" ] ]));
  }

  /**
   * @covers ::a
   */
  public function testAnchorHash() {
    $_SERVER["PATH_INFO"] = "/phpunit";
    $this->assertEquals("<a href='#' class='active'>linktext</a>", $this->invoke($this->abstractPage, "a", [ "#", "linktext", [ "title" => "PHPUnit" ] ]));
  }

  /**
   * @covers ::addClass
   */
  public function testAddClassNoClasses() {
    $attributes = [ ];
    $this->invoke($this->abstractPage, "addClass", [ "phpunit", &$attributes ]);
    $this->assertEquals([ "class" => "phpunit" ], $attributes);
  }

  /**
   * @covers ::addClass
   */
  public function testAddClassClasses() {
    $attributes = [ "class" => "foo bar" ];
    $this->invoke($this->abstractPage, "addClass", [ "phpunit", &$attributes ]);
    $this->assertEquals([ "class" => "foo bar phpunit" ], $attributes);
  }

  /**
   * @covers ::collapseWhitespace
   */
  public function testCollapseWhitespace() {
    $this->assertEquals("p h p u n i t", $this->invoke($this->abstractPage, "collapseWhitespace", [ "    p\nh\rp\tu\x00n\x0Bi \n\r\t\x00\x0Bt    " ]));
  }

  /**
   * @covers ::expandTagAttributes
   */
  public function testExpandTagAttributes() {
    $this->assertEquals(" attr1='phpunit' attr2='true' attr3='false' attr4='&lt;&gt;&amp;' phpunit", $this->invoke($this->abstractPage, "expandTagAttributes", [[
        "attr1" => "phpunit",
        "attr2" => true,
        "attr3" => false,
        "attr4" => "<>&",
        "phpunit",
    ] ]));
  }

  /**
   * @covers ::formatBytes
   * @dataProvider dataProviderFormatBytes
   */
  public function testFormatBytes($number, $unit, $bytes) {
    $this->assertEquals([ $number, $unit ], $this->invoke($this->abstractPage, "formatBytes", [ $bytes ]));
  }

  /**
   * @covers ::getImage
   */
//  public function testGetImage() {
//
//  }

  /**
   * @covers ::getImages
   */
//  public function testGetImages() {
//
//  }

  /**
   * @covers ::getTabindex
   */
  public function testTabindex() {
    $this->assertInternalType("int", $this->invoke($this->abstractPage, "getTabindex"));
  }

  /**
   * @covers ::normalizeLineFeeds
   * @dataProvider dataProviderNormalizeLineFeeds
   */
  public function testNormalizeLineFeeds($expected, $input) {
    $this->assertEquals($expected, $this->invoke($this->abstractPage, "normalizeLineFeeds", [ $input ]));
  }

  /**
   * @covers ::placeholder
   */
  public function testPlaceholder() {
    $this->assertEquals("<em class='placeholder'>&lt;PHP&amp;Unit&gt;</em>", $this->invoke($this->abstractPage, "placeholder", [ "<PHP&Unit>" ]));
  }

  /**
   * @covers ::checkPlain
   * @todo Implement checkPlain
   */
  public function testCheckPlain() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getImage
   * @todo Implement getImage
   */
  public function testGetImage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getTabindex
   * @todo Implement getTabindex
   */
  public function testGetTabindex() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
