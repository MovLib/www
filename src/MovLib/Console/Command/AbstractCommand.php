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
namespace MovLib\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract base class for all MovLib console command classes.
 *
 * Provides several utility methods that can be used by other console command classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCommand extends Command {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Symfony console message type <em>info</em>.
   *
   * Info text will be displayed with a green foreground color.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_INFO = "info";

  /**
   * Symfony console message type <em>comment</em>.
   *
   * Info text will be displayed with a yellow foreground color.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_COMMENT = "comment";

  /**
   * Symfony console message type <em>question</em>.
   *
   * Info text will be displayed with a black foreground color on a cyan background.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_QUESTION = "question";

  /**
   * Symfony console message type <em>error</em>.
   *
   * Info text will be displayed with a white foreground color on a red background.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_ERROR = "error";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Input interface to read from the console.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Output interface to write to the console.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * <code>TRUE</code> if the user requested verbose output, otherwise <code>FALSE</code>.
   *
   * @var boolean
   */
  protected $verbose = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Execute a shell command and exit with the given error message if the returned status code of the executed command
   * is not equal to <var>$status</var>.
   *
   * @param string $command
   *   The command to execute. Note that stderr will automatically be redirected to stdout and only printed in case
   *   of error inside our error message.
   * @param string $errorMessage
   *   The message to display on error.
   * @param int $errorStatus
   *   [Optional] By default any returned status that is not zero (0) will be treated as error.
   * @return this
   */
  protected final function exec($command, $errorMessage, $errorStatus = 0) {
    exec("{$command} 2>&1", $output, $status);
    if ($status !== $errorStatus) {
      // The two empty strings will ensure that Symfony inserts linefeeds. If we'd insert them ourselfs the formatting
      // of the output would be broken.
      $this->exitOnError(array_merge([ $errorMessage, "" ], $output, [ "" ]));
    }
    return $this;
  }

  /**
   * Display error message, rollback everything and exit program.
   *
   * @staticvar boolean $recursion
   *   Helper variable to keep track of calls to this method. If this method is called more than once, we can not call
   *   the rollback method of any child class again. We have to asume that something went wrong during the rollback!
   * @param string $message
   *   The message that should be displayed to the user.
   * @param string $stackTrace
   *   [Optional] The stack trace as returned by the exception that was thrown. Will only be printed to console if the
   *   user called the command with the verbosity flag.
   */
  protected final function exitOnError($message, $stackTrace = "") {
    static $recursion = false;
    if (!is_array($message)) {
      $message = [ $message ];
    }
    array_unshift($message, "ERROR!");
    $message[] = "Rolling back any changes and exiting";
    if ($this->verbose && !empty($stackTrace)) {
      $message[] = $stackTrace;
    }
    $this->write($message, self::MESSAGE_TYPE_ERROR);
    if (method_exists($this, "rollback") && $recursion === false) {
      $this->rollback();
      $recursion = true;
    }
    exit(1);
  }

  /**
   * Helper method for writing and formatting console output.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @param string|array $message
   *   The message that should be displayed to the user.
   * @param string $type
   *   The message type, one of the predefined Symfony console styles (see the class constants <var>MESSAGE_TYPE_*</code>).
   * @return this
   */
  protected final function write($message, $type = self::MESSAGE_TYPE_INFO) {
    $this->output->writeln(is_array($message)
      ? $this->getHelper("formatter")->formatBlock($message, $type, true)
      : "<{$type}>{$message}</{$type}>"
    );
    return $this;
  }

  /**
   * Export input object to class scope.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @return this
   */
  protected final function setInput(InputInterface $input) {
    $this->input = $input;
    return $this;
  }

  /**
   * Export input and output to class scope.
   *
   * @see \MovLib\Console\Command\AbstractCommand::setInput()
   * @see \MovLib\Console\Command\AbstractCommand::setOutput()
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return this
   */
  protected final function setIO(InputInterface $input, OutputInterface $output) {
    return $this->setInput($input)->setOutput($output);
  }

  /**
   * Export output object to class scope.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return this
   */
  protected final function setOutput(OutputInterface $output) {
    $this->output = $output;
    if ($output === OutputInterface::VERBOSITY_VERBOSE) {
      $this->verbose = true;
    }
    return $this;
  }

}
