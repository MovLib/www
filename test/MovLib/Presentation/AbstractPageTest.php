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
 * @coversDefaultClass \MovLib\Presentation\AbstractPage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractPageTest extends \MovLib\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Properties

  /** @var \MovLib\Presentation\Page */
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
    $this->setProperty($this->abstractPage, "title", "PHPUnit");
    $this->assertEquals("PHPUnit — MovLib", $this->invoke($this->abstractPage, "getHeadTitle"));
  }

  /**
   * @covers ::getPresentation
   * @global \MovLib\Tool\Configuration $kernel
   */
  public function testGetPresentation() {
    global $kernel;
    foreach ([ "title" => "PHPUnit", "id" => "phpunit", "bodyClasses" => "phpunit" ] as $property => $value) {
      $this->setProperty($this->abstractPage, $property, $value);
    }
    $presentation = $this->abstractPage->getPresentation();
    $this->assertContains("<!doctype html>", $presentation);
    $this->assertContains("<html dir='ltr' id='nojs' lang='en'>", $presentation);
    $this->assertContains("<head>", $presentation);
    $this->assertContains("<title>PHPUnit — MovLib</title>", $presentation);
    foreach ($this->getProperty($this->abstractPage, "stylesheets") as $stylesheet) {
      $this->assertContains("<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/{$stylesheet}'>", $presentation);
    }
    $this->assertContains("<link rel='icon' type='image/svg+xml' href='//{$kernel->domainStatic}/asset/img/logo/vector.svg'>", $presentation);
    foreach ([ 16, 24, 32, 64, 128, 256 ] as $size) {
      $this->assertContains("<link rel='icon' type='image/png' sizes='{$size}x{$size}' href='//{$kernel->domainStatic}/asset/img/logo/{$size}.png'>", $presentation);
    }
    $this->assertContains("</head>", $presentation);
    $this->assertContains("<body id='phpunit' class='phpunit authenticated'>", $presentation);
  }

  /**
   * @covers ::init
   */
  public function testInit() {
    $this->invoke($this->abstractPage, "init", [ "PHPUnit" ]);
    $this->assertEquals("PHPUnit", $this->getProperty($this->abstractPage, "title"));
  }

}
