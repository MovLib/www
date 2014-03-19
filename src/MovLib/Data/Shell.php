<?php

/*!
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
namespace MovLib\Data;

use \MovLib\Exception\ShellException;

/**
 * Various static methods to interact with the local shell.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Shell {

  /**
   * Execute external command.
   *
   * This method behaves the same as PHP's <code>exec()</code> function, the only differences are that it redirects
   * <i>stderr</i> to <i>stdin</i> and surpresses any output, even upon error. Instead of returning the last
   *
   * @global \MovLib\Kernel $kernel
   * @param string $command
   *   The command to execute.
   * @param null|array $output [output]
   *   If the output argument is present, then the specified array will be filled with every line of output from the
   *   command. Trailing whitespace, such as <code>"\n"</code>, isn't included in this array. Note that if the array
   *   already contains some elements, output will be appended to the end of the array. If you don't want the function
   *   to append elements, call <code>unset()</code> on the array before passing it to this method.
   * @param boolean $exception
   *   Whether to throw an exception upon non-zero exit status or not, defaults to <code>TRUE</code> (throw an
   *   exception).
   * @return integer
   *   The exit status code of the command.
   * @throws \MovLib\Exception\ShellException
   */
  final public static function execute($command, &$output = null, $exception = true) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($command) || !is_string($command)) {
      throw new \InvalidArgumentException("\$command cannot be empty and must be of type string");
    }
    if (isset($output) && !is_array($output)) {
      throw new \InvalidArgumentException("\$output must be of type array");
    }
    if (!is_bool($exception)) {
      throw new \InvalidArgumentException("\$exception must be of type boolean");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Execute the command and redirect stderr to stdin.
    try {
      exec("{$command} 2>&1", $output, $status);

      // Only throw an exception if we are allowed to and if the command had a non-zero exit status.
      if ($status !== 0 && $exception === true) {
        throw new \ShellException($command, $output);
      }

      return $status;
    }
    catch (\Exception $e) {
      if ($exception === true) {
        throw new \ShellException($command, $output);
      }
      return 1;
    }
  }

  /**
   * Execute external command in separate detached thread.
   *
   * This method behaves the same as PHP's <code>exec()</code> function, the only differences are that no output is
   * printed upon error and the executed program will be detached from the current PHP process.
   *
   * <b>IMPORTANT</b><br>
   * If you rely upon the result of the executed program <b>do not</b> use this method, because you can't predict the
   * time of completion and any attempt (e.g. <code>ps aux | grep</code> stuff) to do so is extremely hacky. If you
   * need real threading have a look at {@link http://php.net/book.pthreads.php pthreads}.
   *
   * <b>NOTE</b><br>
   * All file desriptors are directly closed instead of redirected to <code>/dev/null</code>.
   *
   * @link http://stackoverflow.com/questions/222414/asynchronous-shell-exec-in-php
   * @param string $command
   *   The command to execute detached.
   * @throws \MovLib\Exception\ShellException
   */
  final public static function executeDetached($command) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($command) || !is_string($command)) {
      throw new \InvalidArgumentException("\$command cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    try {
      exec("{$command} <&- 1<&- 2<&- &");
    }
    catch (\Exception $e) {
      throw new ShellException($command, null, $e);
    }
  }

  /**
   * Exectue external program and display output.
   *
   * This method behaves the same as PHP's <code>system()</code> function.
   *
   * <b>NOTE</b><br>
   * Don't use this method in FastCGI context, doing so will throw an exception if you're in a development environment.
   *
   * @param string $command
   *   The command to execute.
   * @param boolean $exception
   *   Whether to throw an exception upon non-zero exit status or not, defaults to <code>TRUE</code> (throw an
   *   exception).
   * @return integer
   *   The actual return code of the command.
   * @throws \MovLib\Exception\ShellException
   */
  final public static function executeDisplayOutput($command, $exception = true) {
    // @devStart
    // @codeCoverageIngoreStart
    global $kernel;
    if ($kernel->fastCGI === true) {
      throw new \LogicException("Don't use the Shell::executeDisplayOutput() method in FastCGI context");
    }
    if (empty($command) || !is_string($command)) {
      throw new \InvalidArgumentException("\$command cannot be empty and must be of type string");
    }
    if (!is_bool($exception)) {
      throw new \InvalidArgumentException("\$exception must be of type boolean");
    }
    // @codeCoverageIngoreEnd
    // @devEnd

    // Execute the command and directly display its output.
    try {
      system($command, $status);
      if ($status !== 0 && $exception === true) {
        throw new ShellException($command);
      }
      return $status;
    }
    catch (\Exception $e) {
      if ($exception === true) {
        throw new ShellException($command, null, $e);
      }
      return 1;
    }
  }

}
