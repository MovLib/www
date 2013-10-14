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
namespace MovLib\Test\Data;

use \MovLib\Data\Collator;

/**
 * @coversDefaultClass \MovLib\Data\Collator
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class CollatorTest extends \PHPUnit_Framework_TestCase {

  /**
   * Data provider for all valid sort flag combinations of ksort.
   *
   * @return array
   */
  public function dataProviderTestKsort() {
    return [
      [ \Collator::SORT_REGULAR, null ],
      [ \Collator::SORT_REGULAR, Collator::SORT_REGULAR ],
      [ \Collator::SORT_NUMERIC, Collator::SORT_NUMERIC ],
      [ \Collator::SORT_STRING, Collator::SORT_STRING ],
    ];
  }

  /**
   * @dataProvider dataProviderTestKsort
   * @param int $phpCollatorFlag
   *   The sort flag for the <code>\Collator</code>.
   * @param int $movLibCollatorFlag
   *   The sort flag for the <code>\MovLib\Data\Collator</code>.
   */
  public function testKsort($phpCollatorFlag, $movLibCollatorFlag) {
    global $i18n;
    $this->collator = new Collator($i18n->locale);
    $this->phpCollator = new \Collator($i18n->locale);

    $input = [
      "zPHPUnit" => "PHPUnit1",
      1          => "PHPUnit2",
      "bPHPUnit" => "PHPUnit3",
      "ZPHPUnit" => "PHPUnit4",
      "áPHPUnit" => "PHPUnit5",
      "#PHPUnit" => "PHPUnit6",
      "?PHPUnit" => "PHPUnit7",
      0          => "PHPUnit8",
    ];
    $sorted = $input;
    $keys = array_keys($input);
    $this->phpCollator->sort($keys, $phpCollatorFlag);
    if (isset($movLibCollatorFlag)) {
      $this->collator->ksort($sorted, $movLibCollatorFlag);
    }
    else {
      $this->collator->ksort($sorted);
    }
    $i = 0;
    foreach ($sorted as $k => $v) {
      $this->assertEquals($keys[$i], $k);
      $this->assertEquals($input[$k], $v);
      ++$i;
    }
  }

}
