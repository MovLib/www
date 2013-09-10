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
namespace MovLib\Data\Delayed;

/**
 * Special object to execute method calls after the response was sent to the user.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MethodCalls {

  /**
   * Numeric array to collect the delayed method calls.
   *
   * @var array
   */
  private static $stack = [];

  /**
   * Execute each delayed method.
   */
  public static function run() {
    foreach (self::$stack as list($callable, $params)) {
      call_user_func_array($callable, $params);
    }
  }

  /**
   * Add a delayed method call to the stack.
   *
   * @param object $obj
   *   The object that contains the method which will be executed after the response was sent to the user.
   * @param string $method
   *   The name of the method to call.
   * @param array $params
   *   [Optional] The parameters for the method call.
   */
  public static function stack($obj, $method, $params = []) {
    delayed_register(__CLASS__);
    self::$stack[] = [[ $obj, $method ], $params];
  }

}
