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
namespace MovLib\Test;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase {

  /**
   * @link http://php.net/manual/en/function.password-hash.php
   */
  public function testPasswordCost() {
    $timeTarget = 0.5;
    $cost = 9;
    do {
      $cost++;
      $start = microtime(true);
      password_hash("test", PASSWORD_DEFAULT, [ "cost" => $cost ]);
      $end = microtime(true);
      $actual = $end - $start;
    }
    while ($actual < $timeTarget);
    $this->assertGreaterThanOrEqual(
      $GLOBALS["movlib"]["password_cost"],
      $cost,
      "Please set password_cost in your movlib.ini at least to {$cost} (hashing will take {$actual} seconds)."
    );
  }

}
