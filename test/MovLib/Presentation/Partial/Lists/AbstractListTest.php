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

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Lists\AbstractList
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractListTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $listItems    = [ 1, 2, 3 ];
    $noItemsText  = "PHPUnit";
    $attributes   = [ "attr1", "attr2" ];
    $abstractList = $this->getMockForAbstractClass("\\MovLib\\Presentation\\Partial\\Lists\\AbstractList", [ $listItems, $noItemsText, $attributes ]);
    $this->assertEquals($listItems, $abstractList->listItems);
    $this->assertEquals($noItemsText, $abstractList->noItemsText);
    $this->assertEquals($attributes, $abstractList->attributes);
  }

  /**
   * @covers ::__toString
   * @todo Implement __toString
   */
  public function testToString() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
