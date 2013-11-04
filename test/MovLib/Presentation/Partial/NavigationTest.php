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
namespace MovLib\Presentation\Partial;

use \MovLib\Presentation\Partial\Navigation;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Navigation
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class NavigationTest extends \MovLib\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * @var \MovLib\Presentation\Partial\Navigation
   */
  private $navigation;

  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->navigation = new Navigation("phpunit", "PHPUnit", [[ "/phpunit", "phpunit-linktext", [ "title" => "phpunit-title" ] ] ]);
    $_SERVER["REQUEST_URI"] = "/";
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals("phpunit", $this->navigation->id);
    $this->assertEquals("PHPUnit", $this->navigation->title);
    $this->assertEquals([[ "/phpunit", "phpunit-linktext", [ "title" => "phpunit-title" ] ] ], $this->navigation->menuitems);
  }

  /**
   * @covers ::__toString
   */
  public function testToString() {
    $this->assertEquals(
      "<nav id='phpunit-nav' role='navigation'>" .
      "<h2 class='visuallyhidden' id='phpunit-nav-title'>PHPUnit</h2>" .
      "<div role='menu'>" .
      "<a href='/phpunit' title='phpunit-title' role='menuitem'>phpunit-linktext</a>" .
      "</div>" .
      "</nav>", (string) $this->navigation
    );
  }

  /**
   * @covers ::__toString
   */
  public function testToStringUnorderedList() {
    $this->navigation->unorderedList = true;
    $this->assertEquals(
      "<nav id='phpunit-nav' role='navigation'>" .
      "<h2 class='visuallyhidden' id='phpunit-nav-title'>PHPUnit</h2>" .
      "<div role='menu'>" .
      "<ul class='no-list'>" .
      "<li><a href='/phpunit' title='phpunit-title' role='menuitem'>phpunit-linktext</a></li>" .
      "</ul>" .
      "</div>" .
      "</nav>", (string) $this->navigation
    );
  }

  /**
   * @covers ::__toString
   */
  public function testToStringGlue() {
    $this->navigation->menuitems = [
      [ "/phpunit1", "phpunit-linktext1", [ "title" => "phpunit-title1" ] ],
      [ "/phpunit2", "phpunit-linktext2", [ "title" => "phpunit-title2" ] ],
    ];
    $this->navigation->glue      = " | ";
    $this->assertEquals(
      "<nav id='phpunit-nav' role='navigation'>" .
      "<h2 class='visuallyhidden' id='phpunit-nav-title'>PHPUnit</h2>" .
      "<div role='menu'>" .
      "<a href='/phpunit1' title='phpunit-title1' role='menuitem'>phpunit-linktext1</a>" .
      " | " .
      "<a href='/phpunit2' title='phpunit-title2' role='menuitem'>phpunit-linktext2</a>" .
      "</div>" .
      "</nav>", (string) $this->navigation
    );
  }

  /**
   * @covers ::__toString
   */
  public function testToStringClosure() {
    $this->navigation->callback = function ($menuitem, $index, $total) {
      return [
        "/phpunit{$index}",
        "phpunit-linktext{$index}",
        [ "title" => "phpunit-title{$index}", "data-total" => $total ],
      ];
    };
    $this->assertEquals(
      "<nav id='phpunit-nav' role='navigation'>" .
      "<h2 class='visuallyhidden' id='phpunit-nav-title'>PHPUnit</h2>" .
      "<div role='menu'>" .
      "<a href='/phpunit0' title='phpunit-title0' data-total='1' role='menuitem'>phpunit-linktext0</a>" .
      "</div>" .
      "</nav>", (string) $this->navigation
    );
  }

}
