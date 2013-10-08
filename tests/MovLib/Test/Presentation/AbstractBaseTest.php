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
namespace MovLib\Test\Presentation;

/**
 * @coversDefaultClass \MovLib\Presentation\AbstractBase
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractBaseTest extends \PHPUnit_Framework_TestCase {


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

  /**
   * @coversNothing
   */
  private function invoke($fn, array $args = null) {
    return get_reflection_method($this->abstractPage, $fn)->invokeArgs($this->abstractPage, $args);
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::a
   * @group Presentation
   */
  public function testAnchor() {
    $_SERVER["PATH_INFO"] = "/phpunit";
    $this->assertEquals("<a href='/route' title='PHPUnit'>linktext</a>", $this->invoke("a", [ "/route", "linktext", [ "title" => "PHPUnit" ] ]));
  }

  /**
   * @covers ::a
   * @group Presentation
   */
  public function testAnchorPathInfo() {
    $_SERVER["PATH_INFO"] = "/route";
    $this->assertEquals("<a href='#' class='active'>linktext</a>", $this->invoke("a", [ "/route", "linktext", [ "title" => "PHPUnit" ] ]));
  }

  /**
   * @covers ::a
   * @group Presentation
   */
  public function testAnchorHash() {
    $_SERVER["PATH_INFO"] = "/phpunit";
    $this->assertEquals("<a href='#' class='active'>linktext</a>", $this->invoke("a", [ "#", "linktext", [ "title" => "PHPUnit" ] ]));
  }

  /**
   * @covers ::addClass
   * @group Presentation
   */
  public function testAddClassNoClasses() {
    $attributes = [];
    $this->invoke("addClass", [ "phpunit", &$attributes ]);
    $this->assertEquals([ "class" => "phpunit" ], $attributes);
  }

  /**
   * @covers ::addClass
   * @group Presentation
   */
  public function testAddClassClasses() {
    $attributes = [ "class" => "foo bar" ];
    $this->invoke("addClass", [ "phpunit", &$attributes ]);
    $this->assertEquals([ "class" => "foo bar phpunit" ], $attributes);
  }

  /**
   * @covers ::collapseWhitespace
   * @group Presentation
   */
  public function testCollapseWhitespace() {
    $this->assertEquals("p h p u n i t", $this->invoke("collapseWhitespace", [ "    p\nh\rp\tu\x00n\x0Bi \n\r\t\x00\x0Bt    " ]));
  }

  /**
   * @covers ::expandTagAttributes
   * @group Presentation
   */
  public function testExpandTagAttributes() {
    $this->assertEquals(" attr1='phpunit' attr2='true' attr3='false' attr4='&lt;&gt;&amp;' phpunit", $this->invoke("expandTagAttributes", [[
      "attr1" => "phpunit",
      "attr2" => true,
      "attr3" => false,
      "attr4" => "<>&",
      "phpunit",
    ]]));
  }

  /**
   * @covers ::formatBytes
   * @dataProvider dataProviderFormatBytes
   * @group Presentation
   */
  public function testFormatBytes($number, $unit, $bytes) {
    $this->assertEquals([ $number, $unit ], $this->invoke("formatBytes", [ $bytes ]));
  }

  /**
   * @covers ::getImage
   * @group Presentation
   */
//  public function testGetImage() {
//
//  }

  /**
   * @covers ::getImages
   * @group Presentation
   */
//  public function testGetImages() {
//
//  }

  /**
   * @covers ::getTabindex
   * @group Presentation
   */
  public function testTabindex() {
    $this->assertInternalType("int", get_reflection_method($this->abstractPage, "getTabindex")->invoke($this->abstractPage));
  }

  /**
   * @covers ::normalizeLineFeeds
   * @dataProvider dataProviderNormalizeLineFeeds
   * @group Presentation
   */
  public function testNormalizeLineFeeds($expected, $input) {
    $this->assertEquals($expected, $this->invoke("normalizeLineFeeds", [ $input ]));
  }

  /**
   * @covers ::placeholder
   * @group Presentation
   */
  public function testPlaceholder() {
    $this->assertEquals("<em class='placeholder'>&lt;PHP&amp;Unit&gt;</em>", $this->invoke("placeholder", [ "<PHP&Unit>" ] ));
  }

}
