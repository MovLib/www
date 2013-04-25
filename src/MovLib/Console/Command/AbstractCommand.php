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
 * The abstract base class provides several utility methods that can be used by other console command classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCommand extends Command {

  /**
   * Input interface to read from the console.
   *
   * @var Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Output interface to write to the console.
   *
   * @var Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * <code>true</code> if the user requested verbose output.
   *
   * @var boolean
   */
  protected $verbose = false;

  /**
   * Display error message, rollback everything and exit program.
   *
   * @staticvar boolean $recursion
   *   Helper variable to keep track of calls to this method. If this method is called more than once, we can not call
   *   the rollback method of any child class again. We have to asume that something went wrong during the rollback!
   * @param string $message
   *   The message that should be displayed to the user.
   * @param string $stackTrace
   *   [optional] The stack trace as returned by the exception that was thrown. Will only be printed to console if the
   *   user called the command with the verbosity flag.
   * @return void
   */
  protected final function exitOnError($message, $stackTrace = '') {
    static $recursion = false;
    /* @var $error array */
    $error = [ 'ERROR!', $message, 'Rolling back any changes and exiting.' ];
    if ($this->verbose && !empty($stackTrace)) {
      $error[] = $stackTrace;
    }
    $this->writeError($error);
    if ($recursion === false) {
      $this->rollback();
      $recursion = true;
    }
    exit(0);
  }

  /**
   *
   * @param string|array $message
   *   The message that should be displayed to the user.
   * @param string $type
   *   The message type, one of the predefined Symfony console styles (<tt>info</tt>, <tt>comment</tt>,
   *   <tt>question</tt> or <tt>error</tt>). It is possible to create user defined styles, please refer to the
   *   <a href="http://symfony.com/doc/master/components/console">official Symfony documentation</a> for more info.
   * @return \MovLib\Console\Command\AbstractCommand
   */
  protected final function write($message, $type) {
    if (is_array($message)) {
      /* @var $formatter Symfony\Component\Console\Helper\FormatterHelper */
      $formatter = $this->getHelper('formatter');
      $message = $formatter->formatBlock($message, $type, true);
    } else {
      $message = '<' . $type . '>' . $message . '</' . $type . '>';
    }
    $this->output->writeln($message);
    return $this;
  }

  /**
   * Display info message (green text color).
   *
   * @param string|array $infoMessage
   *   The message that should be displayed to the user.
   * @return \Symfony\Component\Console\Command\Command
   */
  protected final function writeInfo($infoMessage) {
    return $this->write($infoMessage, 'info');
  }

  /**
   * Display comment message (yellow text color).
   *
   * @param string|array $commentMessage
   *   The message that should be displayed to the user.
   * @return \Symfony\Component\Console\Command\Command
   */
  protected final function writeComment($commentMessage) {
    return $this->write($commentMessage, 'comment');
  }

  /**
   * Display question message (black text color on blue background).
   *
   * @param string|array $questionMessage
   *   The message that should be displayed to the user.
   * @return \Symfony\Component\Console\Command\Command
   */
  protected final function writeQuestion($questionMessage) {
    return $this->write($questionMessage, 'question');
  }

  /**
   * Display error message (black text color on red background).
   *
   * @param string|array $errorMessage
   *   The message that should be displayed to the user.
   * @return \MovLib\Console\Command\AbstractCommand
   */
  protected final function writeError($errorMessage) {
    return $this->write($errorMessage, 'error');
  }

  /**
   * Sub-classes can implement this method for automatic rollback of any changes if an error ocurred.
   *
   * @return \Symfony\Component\Console\Command\Command
   */
  protected function rollback() {
    return $this;
  }

  /**
   * Export input object to class scope.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   */
  protected final function setInput(InputInterface $input) {
    $this->input = $input;
  }

  /**
   * Export output object to class scope.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected final function setOutput(OutputInterface $output) {
    $this->output = $output;
    if ($output === OutputInterface::VERBOSITY_VERBOSE) {
      $this->verbose = true;
    }
  }

}
