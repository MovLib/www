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
namespace MovLib\Exception;

/**
 * Shell exceptions are thrown if something goes wrong while interacting with the shell.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ShellException extends \RuntimeException {

  /**
   * Instantiate new shell exception.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $command
   *   The executed command.
   * @param mixed $output [optional]
   *   Either the desired exception code as integer or the output of the command as array or string.
   * @param \Exception $exception [optional]
   *   Any previously thrown exception for chaining.
   */
  public function __construct($command, $output = null, $previous = null) {
    global $kernel;
    $message = $code = null;

    if (is_integer($output)) {
      $code = $output;
    }
    elseif (!empty($output)) {
      // Join the output's lines back together.
      $message = implode("\n", $output) . "\n\n";

      // Replace tabs with four spaces for console output.
      if ($kernel->fastCGI === false) {
        $message = str_replace("\t", "    ", $message);
      }
    }

    parent::__construct("Couldn't execute command: {$command}", $code, $previous);
  }

}
