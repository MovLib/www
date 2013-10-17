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
 * @coversDefaultClass \MovLib\Presentation\AbstractPage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractPageTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\AbstractPage */
  private $abstractPage;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->abstractPage = $this->getMockForAbstractClass("\\MovLib\\Presentation\\AbstractPage");
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::getHeadTitle
    */
  public function testGetHeadTitle() {
    $title = get_reflection_property($this->abstractPage, "title");
    $title->setValue($this->abstractPage, "PHPUnit");
    $this->assertEquals("PHPUnit — MovLib", get_reflection_method($this->abstractPage, "getHeadTitle")->invoke($this->abstractPage));
  }

  /**
   * @covers ::getPresentation
    */
  public function testGetPresentation() {
    get_reflection_property($this->abstractPage, "title")->setValue($this->abstractPage, "PHPUnit");
    get_reflection_property($this->abstractPage, "id")->setValue($this->abstractPage, "phpunit");
    get_reflection_property($this->abstractPage, "bodyClasses")->setValue($this->abstractPage, "phpunit");
    $presentation = $this->abstractPage->getPresentation();
    $this->assertContains("<!doctype html>", $presentation);
    $this->assertContains("<html dir='ltr' id='nojs' lang='en'>", $presentation);
    $this->assertContains("<head>", $presentation);
    $this->assertContains("<title>PHPUnit — MovLib</title>", $presentation);
    foreach (get_reflection_property($this->abstractPage, "stylesheets")->getValue($this->abstractPage) as $stylesheet) {
      $this->assertContains("<link rel='stylesheet' href='{$GLOBALS["movlib"]["static_domain"]}css/{$stylesheet}'>", $presentation);
    }
    $this->assertContains("<link rel='icon' type='image/svg+xml' href='{$GLOBALS["movlib"]["static_domain"]}img/logo/vector.svg'>", $presentation);
    foreach ([ 16, 24, 32, 64, 128, 256 ] as $size) {
      $this->assertContains("<link rel='icon' type='image/png' sizes='{$size}x{$size}' href='{$GLOBALS["movlib"]["static_domain"]}img/logo/{$size}.png'>", $presentation);
    }
    $this->assertContains("</head>", $presentation);
    $this->assertContains("<body id='phpunit' class='phpunit authenticated'>", $presentation);
  }

  /**
   * @covers ::init
    */
  public function testInit() {
    get_reflection_method($this->abstractPage, "init")->invokeArgs($this->abstractPage, [ "PHPUnit" ]);
    //$this->assertEquals([ "abstractpage" ], get_reflection_property($this->abstractPage, "namespace")->getValue($this->abstractPage));
    //$this->assertEquals("abstractpage", get_reflection_property($this->abstractPage, "bodyClasses")->getValue($this->abstractPage));
    //$this->assertEquals("abstractpage", get_reflection_property($this->abstractPage, "id")->getValue($this->abstractPage));
    $this->assertEquals("PHPUnit", get_reflection_property($this->abstractPage, "title")->getValue($this->abstractPage));
  }

}
