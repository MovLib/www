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

use \Worker;

/**
 * Asynchronous worker base class providing base functionality for any asynchronous class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AsyncAbstractWorker extends Worker {

  /**
   * Current instance of the called class. Keeping this private makes sure that nobody will mess with our instance.
   *
   * @var null|$this
   */
  private static $instance = null;

  /**
   * The constructor is kept private because this is a Singleton!
   *
   * Start the thread and make sure our own shutdown method is called upon shutdown of the main PHP thread controlling
   * this asynchronous class instance. The shutdown method is part of our parent Worker class (have a look at the
   * pthreads documentation for more information).
   */
  private function __construct() {
    $this->start();
  }

  /**
   * There is only one instance of a Singleton, cloning is therefor not permitted.
   */
  private function __clone() {}

  /**
   * Get instance of the called class.
   *
   * @return $this
   */
  public static function getInstance() {
    if (self::$instance === null) {
      $class = get_called_class();
      self::$instance = new $class();
    }
    return self::$instance;
  }

  /**
   * Most workers will not need a run method, implement an empty one so we don't have to repeat this all the time.
   */
  public function run() {}

}
