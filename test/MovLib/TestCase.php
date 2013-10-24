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
namespace MovLib;

/**
 * Extension of default PHPUnit Framework TestCase for MovLib providing several useful methods for easy testing of our
 * application.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase {
  use \MovLib\Tool\TraitUtilities;
  use \MovLib\Data\TraitUtilities;

  /**
   * Restore all of our global objects after each test.
   *
   * @global array $backup
   * @global \MovLib\Configuration $config
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function assertPostConditions() {
    global $backup, $config, $i18n, $session;
    foreach ($backup as $k => $v) {
      ${$k} = clone $v;
    }
  }

  /**
   * Execute an external program.
   *
   * This method behaves the same as PHP's <code>exec()</code> function, the only difference is that it redirects
   * <code>stderr</code> to <code>stdout</code> and surpresses any output even upon error.
   *
   * @see \MovLib\Data\TraitUtilities::exec()
   * @param string $command
   *   The external shell program to execute.
   * @param array $output [optional]
   *   If the output argument is present, then the specified array will be filled with every line of output from the
   *   command. Trailing whitespace, such as <code>"\n"</code>, is not included in this array. Note that if the array
   *   already contains some elements, exec will append to the end of the array. If you do not want the function to
   *   append elements, call unset on the array before passing it to exec.
   * @param int $status [optional]
   *   If present the return status of the executed command will be written to this variable.
   * @return boolean
   *   <code>TRUE</code> if the program exited with <code>0</code>, otherwise <code>FALSE</code>
   */
  public static function exec($command, &$output = null, &$status = null) {
    exec("{$command} 2>&1", $output, $status);
    return $status === 0;
  }

  /**
   * Execute an external program in a separate detached thread.
   *
   * This method behaves the same as PHP's <code>exec()</code> function, the only differences are that no output is
   * printed upon error and the executed program will be detached from the current PHP process.
   *
   * <b>IMPORTANT</b>
   * If you rely upon the result of the executed program <b>do not</b> use this method, because you can't predict the
   * time of completion and any attempt (e.g. <code>ps aux | grep</code> stuff) to do so is extremely messy. If you
   * need real threading have a look at {@link http://www.php.net/manual/en/book.pthreads.php pthreads}.
   *
   * <b>NOTE</b>
   * All file descriptors are directly closed instead of redirected to <code>/dev/null</code>.
   *
   * @link http://stackoverflow.com/questions/222414/asynchronous-shell-exec-in-php
   * @see \MovLib\Data\TraitUtilities::execDetached()
   * @param string $command
   *   The external shell program to execute.
   * @return this
   */
  public static function execDetached($command) {
    exec("{$command} <&- 1<&- 2<&-");
  }

  /**
   * Assert that the method returns itself for chaining.
   *
   * @param mixed $object
   *   The object the method should return.
   * @param mixed $actual
   *   The actual return value of the method.
   * @param string $message [optional]
   *   The error message.
   */
  protected function assertChaining($object, $actual, $message = "Method should return containing object for chaining.") {
    $this->assertEquals($object, $actual, $message);
  }

}
