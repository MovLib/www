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

/**
 * @coversDefaultClass \MovLib\Presentation\Page
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PageTest extends \PHPUnit_Framework_TestCase {

  /** @var \MovLib\Presentation\Page */
  public $page;

  public function setUp() {
    $this->page = new Page("PHPUnit");
  }

  /**
   * @covers ::__construct
   * @covers ::init
   * @group Presentation
   */
  public function testConstruct() {
    $this->assertEquals([ "page" ], get_reflection_property($this->page, "namespace")->getValue($this->page));
    $this->assertEquals("page", get_reflection_property($this->page, "bodyClasses")->getValue($this->page));
    $this->assertEquals("page", get_reflection_property($this->page, "id")->getValue($this->page));
    $this->assertEquals("PHPUnit", get_reflection_property($this->page, "title")->getValue($this->page));
    $this->assertContains("<noscript>", $this->page->alerts);
  }

  /**
   * @covers ::checkErrors
   * @group Presentation
   */
  public function checkErrors() {
    $this->assertFalse($this->page->checkErrors(null));
    $this->assertTrue($this->page->checkErrors([ "<phpunit>" ]));
    $this->assertContains("<phpunit>", $this->page->alerts);
  }

}
