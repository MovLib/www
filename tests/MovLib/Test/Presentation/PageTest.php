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

use \MovLib\Presentation\Page;
use \ReflectionMethod;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PageTest extends \PHPUnit_Framework_TestCase {

  /** @var \MovLib\Presentation\Page */
  public $page;

  public static function dataProviderNormalizeLineFeeds() {
    return [
      [ "\n", "\n" ],
      [ "\n", "\r" ],
      [ "\n", "\r\n" ],
      [ "mov\nlib", "mov\nlib" ],
      [ "mov\nlib", "mov\rlib" ],
      [ "mov\nlib", "mov\r\nlib" ],
    ];
  }

  public function setUp() {
    $this->page = new Page("PHPUnit");
  }

  /**
   * @covers \MovLib\Presentation\Page::__construct
   * @covers \MovLib\Presentation\Page::init
   * @covers \MovLib\Presentation\AbstractPage::init
   */
  public function testConstruct() {
    $this->assertEquals([ "page" ], get_reflection_property($this->page, "namespace")->getValue($this->page));
    $this->assertEquals("page", get_reflection_property($this->page, "bodyClasses")->getValue($this->page));
    $this->assertEquals("page", get_reflection_property($this->page, "id")->getValue($this->page));
    $this->assertEquals("PHPUnit", get_reflection_property($this->page, "title")->getValue($this->page));
    $this->assertContains("<noscript>", $this->page->alerts);
  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::a
   */
//  public function testA() {
//
//  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::addClass
   */
//  public function testAddClass() {
//
//  }

  /**
   * @covers \MovLib\Presentation\Page::checkErrors
   */
  public function checkErrors() {
    $this->assertFalse($this->page->checkErrors(null));
    $this->assertTrue($this->page->checkErrors([ "<phpunit>" ]));
    $this->assertContains("<phpunit>", $this->page->alerts);
  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::checkPlain
   */
  public function testCheckPlain() {
    $rm = new ReflectionMethod($this->page, "checkPlain");
    $rm->setAccessible(true);
    $this->assertEquals(
      "test&quot;string&quot;with&lt;html&gt;embedded&lt;script&gt;reserved&apos;&apos;tags&apos;&apos;",
      $rm->invoke($this->page, "test\"string\"with<html>embedded<script>reserved''tags''")
    );
  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::expandTagAttributes
   */
//  public function testExpandTagAttributes() {
//
//  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::getImage
   */
//  public function testGetImage() {
//
//  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::getTabindex
   */
//  public function testGetTabindex() {
//    $rm = new ReflectionMethod($this->page, "getTabindex");
//    $rm->setAccessible(true);
//    $this->assertEquals(1, $rm->invoke($this->page));
//  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::normalizeLineFeeds
   * @dataProvider dataProviderNormalizeLineFeeds
   */
  public function testNormalizeLineFeeds($expected, $testString) {
    $rm = new ReflectionMethod($this->page, "normalizeLineFeeds");
    $rm->setAccessible(true);
    $this->assertEquals($expected, $rm->invoke($this->page, $testString));
  }

  /**
   * @covers \MovLib\Presentation\AbstractPage::placeholder
   */
  public function testPlaceholder() {
    $rm = new ReflectionMethod($this->page, "placeholder");
    $rm->setAccessible(true);
    $this->assertEquals("<em class='placeholder'>Placeholder &lt;Test&gt;</em>", $rm->invoke($this->page, "Placeholder <Test>"));
  }

}
