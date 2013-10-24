<?php

/* !
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
namespace MovLib\Presentation\Partial\Lists;

use \MovLib\Presentation\Partial\Lists\Ordered;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Lists\Ordered
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class OrderedTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   * @covers \MovLib\Presentation\Partial\Lists\AbstractList::__construct
   */
  public function testConstruct() {
    $listItemsAttributes = [ "attr1", "attr2" ];
    $orderedList         = new Ordered([ ], null, null, $listItemsAttributes);
    $this->assertEquals($listItemsAttributes, $orderedList->listItemsAttributes);
  }

  public function testTagProperty() {
    $orderedList = new Ordered([ ]);
    $this->assertEquals("ol", $this->getProperty($orderedList, "tag"));
  }

  /**
   * @covers ::__toString
   */
  public function testToStringNoAttributes() {
    $orderedList  = new Ordered([ "PHPUnit" ]);
    $tag          = "phpunit";
    $this->setProperty($orderedList, "tag", $tag);
    $expectedList = "<{$tag}><li>PHPUnit</li></{$tag}>";
    $this->assertEquals($expectedList, $orderedList->__toString());
  }

  /**
   * @covers ::__toString
   */
  public function testToStringNoItems() {
    $orderedList = new Ordered([ ]);
    $this->assertNull($orderedList->__toString());

    $noItemsText = "PHPUnit";
    $orderedList = new Ordered([ ], $noItemsText);
    $this->assertEquals($noItemsText, $orderedList->__toString());
  }

  /**
   * @covers ::__toString
   */
  public function testToStringWithAttributes() {
    $attributes         = [ "class" => "PHPUnit", "test" => "PHPUnit" ];
    $expandedAttributes = "";
    foreach ($attributes as $attribute => $value) {
      $expandedAttributes .= " {$attribute}='{$value}'";
    }
    $expectedList = "<ol{$expandedAttributes}><li>PHPUnit</li></ol>";
    $orderedList  = new Ordered([ "PHPUnit" ], "", $attributes);
    $this->assertEquals($expectedList, $orderedList->__toString());
  }

  /**
   * @covers ::__toString
   */
  public function testToStringWithClosure() {
    $listItems            = [ 1, 2, "PHPUnit", "test" ];
    $orderedList          = new Ordered($listItems);
    $orderedList->closure = function ($item, $index, $count) {
      return "{$item}{$index}{$count}";
    };
    $c            = count($listItems);
    $expectedList = "";
    for ($i = 0; $i < $c; ++$i) {
      $expectedList .= "<li>{$listItems[$i]}{$i}{$c}</li>";
    }
    $expectedList = "<ol>{$expectedList}</ol>";
    $this->assertEquals($expectedList, $orderedList->__toString());
  }

  /**
   * @covers ::__toString
   */
  public function testToStringWithListItemsAttributes() {
    $listItemsAttributes = [ "class" => "PHPUnit", "test" => "PHPUnit" ];
    $expandedAttributes  = "";
    foreach ($listItemsAttributes as $attribute => $value) {
      $expandedAttributes .= " {$attribute}='{$value}'";
    }
    $listItems    = [ 1, 2, "PHPUnit", "test" ];
    $expectedList = "";
    $c            = count($listItems);
    for ($i = 0; $i < $c; ++$i) {
      $expectedList .= "<li{$expandedAttributes}>{$listItems[$i]}</li>";
    }
    $expectedList = "<ol>{$expectedList}</ol>";
    $orderedList  = new Ordered($listItems, "", null, $listItemsAttributes);
    $this->assertEquals($expectedList, $orderedList->__toString());
  }

}
