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
namespace MovLib\Tool\Console\Command;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract base class for all MovLib console command classes.
 *
 * Provides several utility methods that can be used by other console command classes.
 *
 * @property \Symfony\Component\Console\Helper\DialogHelper $dialog
 *   Symfony dialog helper for asking questions etc..
 * @property \Symfony\Component\Console\Helper\ProgressHelper $progress
 *   Display nice progress bar during long script execution.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command {
  use \MovLib\Data\TraitUtilities;
  use \MovLib\Tool\TraitUtilities;


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
   * Symfony dialog helper.
   *
   * @see AbstractCommand::dialog()
   * @var \Symfony\Component\Console\Helper\DialogHelper
   */
  private $dialog;

  /**
   * Input interface to read from the console.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Symfony progress helper.
   *
   * @see AbstractCommand::progress()
   * @var \Symfony\Component\Console\Helper\ProgressHelper
   */
  private $progress;

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


  /**
   * Instantiate new CLI command.
   *
   * @internal No clue why the Symfony guys made <var>$name</var> optional, just to throw an exception?
   * @param string $name
   *   The command name.
   */
  public function __construct($name) {
    parent::__construct($name);
    $this->setAliases([ $this->getShortcut($name) ]);
  }

  /**
   * Automatically called by PHP if trying to access an inaccessible property.
   *
   * @param string $name
   *   The name of the inaccessible property.
   * @return mixed
   *   The value of the inaccessible property.
   * @throws \RuntimeException
   */
  public function __get($name) {
    if (isset($this->{$name})) {
      return $this->{$name};
    }
    if (method_exists($this, $name)) {
      return $this->{$name}();
    }
    throw new \RuntimeException;
  }


  // ------------------------------------------------------------------------------------------------------------------- Magic Property Getters


  /**
   * Automatically called via <code>__get()</code>.
   *
   * @see AbstractCommand::__get()
   * @return \Symfony\Component\Console\Helper\DialogHelper
   *   Dialog helper instance.
   */
  private function dialog() {
    if (!$this->dialog) {
      $this->dialog = $this->getHelperSet()->get("dialog");
    }
    return $this->dialog;
  }

  /**
   * Automatically called via <code>__get()</code>.
   *
   * @see AbstractCommand::__get()
   * @return \Symfony\Component\Console\Helper\ProgressHelper
   *   Progress helper instance.
   */
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
     * Add an option to this command.
     *
     * Replacement for the <code>addOption</code> method with auto-generated shortcuts. No checks are performed, you'll
     * get an exception anyways if something is really odd and if your shortcut is messy you'll get complains from
     * others.
     *
     * @see AbstractCommand::addOption()
     * @param string $name
     *   The option's name.
     * @param integer $mode
     *   The option's mode: One of the <code>InputOption::VALUE_*</code> constants.
     * @param string $description
     *   The option's description text.
     * @param mixed $default
     *   The default value (must be null for <code>InputOption::VALUE_REQUIRED</code> or <code>InputOption::VALUE_NONE<code>).
     * @return this
     */
  public final function addInputOption($name, $mode = null, $description = "", $default = null) {
    return $this->addOption($name, $this->getShortcut($name), $mode, $description, $default);
  }

  /**
   * Ask a question.
   *
   * @param string $question
   *   The question to ask.
   * @param string $default [optional]
   *   The default answer.
   * @param array $autocomplete [optional]
   *   Autocomplete values for possible answers.
   * @return string
   *   The answer.
   */
  protected final function ask($question, $default = null, array $autocomplete = null) {
    $defaultDisplay = $default ? " [default: {$default}]" : null;
    return $this->dialog->ask($this->output, "<question>{$question}</question>{$defaultDisplay} ", $default, $autocomplete);
  }

  /**
   * Ask to confirm.
   *
   * @param string $question
   *   The question to ask.
   * @param string $default [optional]
   *   The default answer, defaults to <code>TRUE</code>.
   * @return boolean
   *   The answer.
   */
  protected final function askConfirmation($question, $default = true) {
    $defaultDisplay = $default ? "y" : "n";
    return $this->dialog->askConfirmation($this->output, "<question>{$question}</question> [default: {$defaultDisplay}] ", $default);
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
  protected final function askWithChoices($text, $default = null, array $choices = null, array $choiceExplanations = null) {
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
   * Automatically called by Symfony if this command was called.
   *
   * @param InputInterface $input
   *   The active input instance.
   * @param OutputInterface $output
   *   The active output instance.
   * @return this
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;
    if ($output->getVerbosity() <= OutputInterface::VERBOSITY_VERBOSE) {
      $this->verbose = true;
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
   * @param \Exception $exception [optional]
   *   The exception that occurred, if any. The stacktrace is only printed if the user called the command with the
   *   verbosity option.
   */
  protected final function exitOnError($message, \Exception $exception = null) {
    static $recursion = false;
    if (!is_array($message)) {
      $message = [ $message ];
    }
    array_unshift($message, "ERROR!");
    $message[] = "Rolling back any changes and exiting";
    if ($this->verbose === true && $exception) {
      foreach (explode("\n", $exception->getTraceAsString()) as $line) {
        $message[] = $line;
      }
    }
    $this->write($message, self::MESSAGE_TYPE_ERROR);
    if (method_exists($this, "rollback") && $recursion === false) {
      $recursion = true;
      $this->rollback();
    }
    exit(1);
  }

  /**
   * Get shortcut for argument or option.
   *
   * @param string $name
   *   The name of the argument or option.
   * @return string
   *   The shortcut.
   * @throws \InvalidArgumentException
   */
  protected final function getShortcut($name) {
    if (empty($name)) {
      throw new \InvalidArgumentException;
    }
    $shortcut = null;
    foreach (explode("-", $name) as $namePart) {
      $shortcut .= $namePart[0];
    }
    return $shortcut;
  }

  /**
   * Check if an optional option is set.
   *
   * @global array $argv
   * @param string $name
   *   The option's name.
   * @param mixed $value
   *   The value of the option will be stored in this variable if set.
   * @return mixed
   *   <code>FALSE</code> if the option is not set, otherwise the options value.
   */
  protected final function issetOption($name, &$value) {
    global $argv;
    if (array_search("--{$name}", $argv) || array_search("-{$this->getShortcut($name)}", $argv)) {
      $value = $this->input->getOption($name);
      if (empty($value)) {
        $value = null;
      }
      return true;
    }
    return false;
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

}
