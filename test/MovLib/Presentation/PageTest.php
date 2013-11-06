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

use \MovLib\Presentation\Page;

/**
 * @coversDefaultClass \MovLib\Presentation\Page
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PageTest extends \MovLib\TestCase {

  /** @var \MovLib\Presentation\Page */
  public $page;

  public function setUp() {
    $this->page = new Page("PHPUnit");
  }

  /**
   * @covers ::__construct
   * @covers ::init
   */
  public function testConstruct() {
    foreach ([
    "namespace"   => [ "page" ],
    "bodyClasses" => "page",
    "id"          => "page",
    "title"       => "PHPUnit",
    ] as $property => $value) {
      $this->assertEquals($value, $this->getProperty($this->page, $property));
    }
    $this->assertContains("<noscript>", $this->page->alerts);
  }

  /**
   * @covers ::checkErrors
   */
  public function checkErrors() {
    $this->assertFalse($this->page->checkErrors(null));
    $this->assertTrue($this->page->checkErrors([ "<phpunit>" ]));
    $this->assertContains("<phpunit>", $this->page->alerts);
  }

  /**
   * @covers ::getBreadcrumb
   * @todo Implement getBreadcrumb
   */
  public function testGetBreadcrumb() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getBreadcrumbs
   * @todo Implement getBreadcrumbs
   */
  public function testGetBreadcrumbs() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getContent
   * @todo Implement getContent
   */
  public function testGetContent() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getFooter
   * @todo Implement getFooter
   */
  public function testGetFooter() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getHeader
   * @todo Implement getHeader
   */
  public function testGetHeader() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getHeaderLogo
   * @todo Implement getHeaderLogo
   */
  public function testGetHeaderLogo() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getPresentation
   * @todo Implement getPresentation
   */
  public function testGetPresentation() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getWrappedContent
   * @todo Implement getWrappedContent
   */
  public function testGetWrappedContent() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::init
   * @todo Implement init
   */
  public function testInit() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::formatFooterSystemLanguage
   * @todo Implement formatFooterSystemLanguage
   */
  public function testFormatFooterSystemLanguage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
