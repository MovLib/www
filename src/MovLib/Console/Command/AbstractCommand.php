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

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract base class for all MovLib console command classes.
 *
 * Provides several utility methods that can be used by other console command classes.
 *
 * @property \Symfony\Component\Console\Helper\ProgressHelper $progress
 *   Symfony ProgressHelper.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Symfony console message type <i>info</i>.
   *
   * Info text will be displayed with a green foreground color.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_INFO = "info";

  /**
   * Symfony console message type <i>comment</i>.
   *
   * Info text will be displayed with a yellow foreground color.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_COMMENT = "comment";

  /**
   * Symfony console message type <i>question</i>.
   *
   * Info text will be displayed with a black foreground color on a cyan background.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @var string
   */
  const MESSAGE_TYPE_QUESTION = "question";

  /**
   * Symfony console message type <i>error</i>.
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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __get($name) {
    if (isset($this->{$name})) {
      return $this->{$name};
    }
    if (method_exists($this, $name)) {
      return $this->{$name}();
    }
    throw new \RuntimeException;
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods And Properties


  private $progress;
  private function progress() {
    if (!$this->progress) {
      $this->progress = $this->getHelperSet()->get("progress");
      $this->progress->setBarCharacter("<comment>=</comment>");
      $this->progress->setBarWidth(120);
    }
    return $this->progress;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get Symfony DialogHelper instance.
   *
   * @staticvar null|\Symfony\Component\Console\Helper\DialogHelper $dialog
   *   Used to cache the object.
   * @return \Symfony\Component\Console\Helper\DialogHelper
   */
  function getDialog() {
    static $dialog = null;
    if (!$dialog) {
      $dialog = $this->getHelperSet()->get("dialog");
    }
    return $dialog;
  }

  /**
   * Asks a question to the user.
   *
   * @see \Symfony\Component\Console\Helper\DialogHelper::ask()
   * @param string $question
   *   The question to ask.
   * @param string $default [optional]
   *   The default answer.
   * @param array $autocomplete [optional]
   *   Autocomplete values.
   * @return string
   *   The user's answer.
   */
  function ask($question, $default = null, array $autocomplete = null) {
    return $this->getDialog()->ask($this->output, "<question>{$question}</question> [default: {$default}] ", $default, $autocomplete);
  }

  /**
   * Ask user to confirm.
   *
   * @see \Symfony\Component\Console\Helper\DialogHelper::askConfirmation()
   * @param string $question
   *   The question to ask the user.
   * @param boolean $default [optional]
   *   The default answer, defaults to <code>TRUE</code>.
   * @return boolean
   *   <code>TRUE</code> if the user answered yes, otherwise <code>FALSE</code>.
   */
  function askConfirmation($question, $default = true) {
    $d = $default === true ? "y" : "n";
    return $this->getDialog()->askConfirmation($this->output, "<question>{$question}</question> [default: {$d}] ", $default);
  }

  /**
   * Ask a question to the user, displaying all choice possibilities.
   *
   * @param string $text
   *   The heading of the choice list.
   * @param string $default [optional]
   *   The default answer.
   * @param array $choices [optional]
   *   The available choices (also used for autocompletion) as numeric array.
   * @param array $choiceExplanations [optional]
   *   The explanations for the available choices as numeric array..
   * @return string
   *   The user's answer.
   */
  function askWithChoices($text, $default = null, array $choices = null, array $choiceExplanations = null) {
    $this->write($text, self::MESSAGE_TYPE_COMMENT)->write("Possible choices are:\n", self::MESSAGE_TYPE_COMMENT);
    if ($choices && $choiceExplanations){
      $c = count($choices);
      for ($i = 0; $i < $c; ++$i) {
        $this->write("{$choices[$i]}: {$choiceExplanations[$i]}");
      }
    }
    else {
      $c = count($choices);
      for ($i = 0; $i < $c; ++$i) {
        $this->write($choices[$i]);
      }
    }
    return $this->write("")->ask("Which one should it be?", $default ?: "none", $choices);
  }

  /**
   * Check if the executing user has root privileges.
   *
   * @return this
   */
  protected final function checkPrivileges() {
    if (posix_getuid() !== 0) {
      $this->exitOnError("This script must be executed as privileged user (root or sudo).");
    }
    return $this;
  }

  /**
   * Execute a shell command and surpress output.
   *
   * @param string $command
   *   The command to execute. Note that stderr will automatically be redirected to stdout and only printed in case
   *   of error inside our error message.
   * @param string $errorMessage
   *   The message to display on error.
   * @param array $options [optional]
   *   Associative array with options to change the behaviour of this method, available options are:
   *   <ul>
   *     <li><code>"exit_on_error"</code> controls what to do if the executed command fails. If set to <code>TRUE</code>
   *     (default) the command execution will be stopped by calling <code>$this->exitOnError()</code> with the supplied
   *     error message. If set to <code>FALSE</code> the error message will be printed and the user will be asked if
   *     execution should be seized or continued.</li>
   *     <li><code>"return_status"</code> controls what will be returned. If set to <code>FALSE</code> (default) the
   *     current instance (<code>$this</code>) will be returned. If set to <code>TRUE</code> the exit status of the
   *     executed command will be returned. This is useful if you have dependencies on the success or failure of this
   *     command.</li>
   *   </ul>
   * @return this|int
   *   Depending on <code>$options["return_status"]</code>, defaults to <code>$this</code>.
   */
  protected final function exec($command, $errorMessage, array $options = []) {
    $options = array_merge([
      "exit_on_error" => true,
      "return_status" => false,
    ], $options);
    exec("{$command} 2>&1", $output, $status);
    if ($status !== 0) {
      // The two empty strings will ensure that Symfony inserts linefeeds. If we'd insert them ourselfs the formatting
      // of the output would be broken.
      $errorMessage = array_merge([ $errorMessage, "" ], $output, [ "" ]);
      if ($options["exit_on_error"] === true) {
        $this->exitOnError($errorMessage);
      }
      else {
        $this->write($errorMessage, self::MESSAGE_TYPE_ERROR);
        if ($this->askConfirmation("Continue execution?") === false) {
          $this->exitOnError("As you wish ...");
        }
      }
    }
    if ($options["return_status"]) {
      return $status;
    }
    return $this;
  }

  /**
   * Execute a shell command and display output.
   *
   * @param string $command
   *   The command to execute.
   * @param string $errorMessage
   *   The message to display on error.
   * @param array $options [optional]
   *   Associative array with options to change the behaviour of this method, available options are:
   *   <ul>
   *     <li><code>"exit_on_error"</code> controls what to do if the executed command fails. If set to <code>TRUE</code>
   *     (default) the command execution will be stopped by calling <code>$this->exitOnError()</code> with the supplied
   *     error message. If set to <code>FALSE</code> the error message will be printed and the user will be asked if
   *     execution should be seized or continued.</li>
   *     <li><code>"return_status"</code> controls what will be returned. If set to <code>FALSE</code> (default) the
   *     current instance (<code>$this</code>) will be returned. If set to <code>TRUE</code> the exit status of the
   *     executed command will be returned. This is useful if you have dependencies on the success or failure of this
   *     command.</li>
   *   </ul>
   * @return this|int
   *   Depending on <code>$options["return_status"]</code>, defaults to <code>$this</code>.
   */
  protected final function system($command, $errorMessage, array $options = []) {
    $options = array_merge([
      "exit_on_error" => true,
      "return_status" => false,
    ], $options);
    system($command, $status);
    if ($status !== 0) {
      if ($options["exit_on_error"] === true) {
        $this->exitOnError($errorMessage);
      }
      else {
        $this->write($errorMessage, self::MESSAGE_TYPE_ERROR);
        if ($this->askConfirmation("Continue execution?") === false) {
          $this->exitOnError("As you wish ...");
        }
      }
    }
    if ($options["return_status"]) {
      return $status;
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
   * @param string $stackTrace [optional]
   *   The stack trace as returned by the exception that was thrown. Will only be printed to console if the user called
   *   the command with the verbosity flag.
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
      $recursion = true;
      $this->rollback();
    }
    exit(1);
  }

  /**
   * Helper method for writing and formatting console output.
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @param string|array $message
   *   The message that should be displayed to the user.
   * @param string $type [optional]
   *   The message type, one of the predefined Symfony console styles (see the class constants <var>MESSAGE_TYPE_*</var>).
   *   Defaults to no style (white text).
   * @return this
   */
  protected final function write($message, $type = null) {
    if (is_array($message)) {
      $message = $this->getHelper("formatter")->formatBlock($message, $type, true);
    }
    elseif ($type) {
      $message = "<{$type}>{$message}</{$type}>";
    }
    $this->output->writeln($message);
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
    if ($output->getVerbosity() <= OutputInterface::VERBOSITY_VERBOSE) {
      $this->verbose = true;
    }
    return $this;
  }

}
