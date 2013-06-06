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
namespace MovLib\Utility;

/**
 * Abstract asynchronous class providing base functionality for any asynchronous class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractDelayed {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Current instance of the called class. Keeping this private makes sure that nobody will mess with our instance.
   *
   * @var null|\MovLib\Utility\DelayedLogger
   */
  private static $instance = null;

  /**
   * The stack contains all unformatted email message that were collected throughout the request.
   *
   * @var string
   */
  public $stack = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Singleton!
   */
  private function __construct(){}

  /**
   * Singleton!
   */
  private function __clone(){}

  /**
   * Destroy current instance.
   */
  public function __destruct() {
    self::$instance = null;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Get instance of the called class.
   *
   * @global array $delayedObjects
   *   Global array to collect delayed objects for execusion after response was sent to the user.
   * @return \MovLib\Utility\DelayedLogger
   */
  public static function getInstance() {
    global $delayedObjects;
    if (self::$instance === null) {
      $class = get_called_class();
      self::$instance = new $class();
      $delayedObjects[] = self::$instance;
    }
    return self::$instance;
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Every asynchronous class has to implement a run method.
   */
  abstract public function run();

}
