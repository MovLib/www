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

  /**
   * Test shorten method.
   */
  public function testShorten() {
    $this->assertEquals("Iñtërnâ…", String::shorten("Iñtërnâtiônàlizætiøn and then the quick brown fox jumped", "…"));
  }

  /**
   * Test wordwrap without cutting long words.
   */
  public function testWordwrapNoCut() {
    $this->assertEquals(
      "Iñtërnâtiônàlizætiøn
and then
the quick
brown fox
jumped
overly the
lazy dog
and one
day the
lazy dog
humped the
poor fox
down until
she died.",
      String::wordwrap("Iñtërnâtiônàlizætiøn and then the quick brown fox jumped overly the lazy dog and one day the lazy dog humped the poor fox down until she died.", 10)
    );
  }

  /**
   * Test wordwrap with cutting long words.
   */
  public function testWordwrapCut() {
    $this->assertEquals(
      "Iñtërnâ
tiônàliz
ætiøn_an
d_then_the
_quick_bro
wn_fox_jum
ped_overly
_the_lazy_
dog and
one day
the lazy
dog humped
the poor
fox down
until she
died.",
      String::wordwrap("Iñtërnâtiônàlizætiøn and then the quick brown fox jumped overly the lazy dog and one day the lazy dog humped the poor fox down until she died.", 10, PHP_EOL, true)
    );
  }

}
