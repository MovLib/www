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

use \MovLib\Presentation\Partial\Navigation;
use \MovLib\Presentation\Home;

/**
 * @coversDefaultClass \MovLib\Presentation\Home
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HomeTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\Home */
  private $home;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $_SERVER["PATH_INFO"] = "/";
    $this->home = new Home();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
    */
  public function testConstruct() {
    $this->assertEquals("MovLib", $this->getProperty($this->home, "title"));
    $this->assertTrue(in_array("modules/home.css", $this->getProperty($this->home, "stylesheets")));
  }

  /**
   * @covers ::getBreadcrumb
    */
  public function testGetBreadcrumb() {
    $breadcrumb = new Navigation("breadcrumb", "You are here: ", [[ "/", "Home", [ "title" => "Go back to the home page." ] ]]);
    $breadcrumb->attributes["class"] = "container";
    $breadcrumb->glue = " › ";
    $breadcrumb->hideTitle = false;
    $this->assertEquals($breadcrumb, $this->invoke($this->home, "getBreadcrumb"));
  }

  /**
   * @covers ::getHeaderLogo
    */
  public function testGetHeaderLogo() {
    $this->assertEquals(
      "<h1 class='span' id='header__logo'><img alt='MovLib, the free movie library.' height='42' id='logo' src='{$GLOBALS["movlib"]["static_domain"]}img/logo/vector.svg' width='42'> MovLib</h1>",
        $this->invoke($this->home, "getHeaderLogo")
      );
  }

  /**
   * @covers ::getHeadTitle
    */
  public function testGetHeadTitle() {
    $this->assertEquals("MovLib, the free movie library.", $this->invoke($this->home, "getHeadTitle"));
  }

  /**
   * @covers ::getWrappedContent
    */
  public function testGetWrappedContent() {
    $wrappedContent = $this->invoke($this->home, "getWrappedContent");
    $this->assertContains("class='home-content'", $wrappedContent);
    $this->assertContains("id='home-banner'", $wrappedContent);
    $this->assertContains("id='alerts'", $wrappedContent);
    $this->assertContains("class='container container--home'", $wrappedContent);
  }

}
