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
namespace MovLib\Presentation\Partial\Lists;

use \MovLib\Presentation\Partial\Lists\GlueSeparated;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Lists\GlueSeparated
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class GlueSeparatedTest extends \MovLib\TestCase {

  public function testConstruct() {
    $glue       = "PHPUnit glue";
    $listBefore = "PHPUnit before";
    $listAfter  = "PHPUnit after";
    $gsList     = new GlueSeparated([ ], "", $glue, $listBefore, $listAfter);
    $this->assertEquals($glue, $gsList->glue);
    $this->assertEquals($listBefore, $gsList->listBefore);
    $this->assertEquals($listAfter, $gsList->listAfter);
  }

  /**
   * @covers ::__toString
   */
  public function testToStringNoItems() {
    $gsList = new GlueSeparated([ ]);
    $this->assertNull($gsList->__toString());

    $noItemsText = "PHPUnit";
    $gsList      = new GlueSeparated([ ], $noItemsText);
    $this->assertEquals($noItemsText, $gsList->__toString());
  }

  /**
   * @covers ::__toString
   */
  public function testToStringWithBeforeAndAfterAndGlueing() {
    $glue         = "glue ";
    $listBefore   = "before";
    $listAfter    = "after";
    $gsList       = new GlueSeparated([ "PHPUnit" ], null, $glue, $listBefore, $listAfter);
    $expectedList = "{$listBefore}PHPUnit{$listAfter}";
    $this->assertEquals($expectedList, $gsList->__toString());

    $gsList       = new GlueSeparated([ "PHPUnit", "PHPUnit" ], null, $glue, $listBefore, $listAfter);
    $expectedList = "{$listBefore}PHPUnit{$glue}PHPUnit{$listAfter}";
    $this->assertEquals($expectedList, $gsList->__toString());
  }

  /**
   * @covers ::__toString
   */
  public function testToStringWithClosure() {
    $listItems = [ "PHPUnit", 1, "test" ];
    $glue      = "glue ";
    $gsList    = new GlueSeparated($listItems, null, $glue);
    $closure   = function ($item, $index, $count) {
      return "{$item}{$index}{$count}";
    };
    $gsList->closure = $closure;
    $c               = count($listItems);
    $expectedList    = [ ];
    for ($i = 0; $i < $c; ++$i) {
      $expectedList[] = "{$listItems[$i]}{$i}{$c}";
    }
    $expectedList = implode($glue, $expectedList);
    $this->assertEquals($expectedList, $gsList->__toString());
  }

}
