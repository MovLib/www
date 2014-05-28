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
namespace MovLib\Console\Command;

use \MovLib\Core\Container;
use \MovLib\Core\Shell;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\Output;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract base class for all MovLib console command classes.
 *
 * Provides several utility methods that can be used by other console command classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractCommand";
  // @codingStandardsIgnoreEnd


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
   * Active global configuration instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\Container
   */
  protected $container;

  /**
   * Active file system instance.
   *
   * @var \MovLib\Core\FileSystem
   */
  protected $fs;

  /**
   * Input interface to read from the console.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * Active CLI kernel instance.
   *
   * @var \MovLib\Core\Kernel
   */
  protected $kernel;

  /**
   * Active log instance.
   *
   * @var \MovLib\Core\Log
   */
  protected $log;

  /**
   * Whether the current process is executed with elevated privileges or not.
   *
   * @var boolean
   */
  protected $privileged = false;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new CLI command.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   */
  public function __construct(Container $container) {
    $this->container  = $container;
    $this->config     = $container->config;
    $this->fs         = $container->fs;
    $this->intl       = $container->intl;
    $this->kernel     = $container->kernel;
    $this->log        = $container->log;
    $this->privileged = posix_getuid() === 0;
    parent::__construct();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Starts the progress output.
   *
   * @param type $max
   */
  final protected function progressStart($max = null) {
    if ($this->output) {
      $this->progress = $this->getHelperSet()->get("progress");
      $this->progress->setBarCharacter("<comment>=</comment>");
      $this->progress->setBarWidth(120);
      $this->progress->start($this->output, $max);
    }
    return $this;
  }

  /**
   * Advance the progress output by <var>$steps</var>.
   *
   * @param integer $steps [optional]
   *   The amount of steps to advance, defaults to <code>1</code>.
   * @param boolean $redraw [optional]
   *   Whether to redraw the progress output or not.
   * @return this
   */
  final protected function progressAdvance($steps = 1, $redraw = false) {
    if ($this->progress) {
      $this->progress->advance($steps, $redraw);
    }
    return $this;
  }

  /**
   * Finishes the progress output.
   *
   * @return this
   */
  final protected function progressFinish() {
    if ($this->progress) {
      $this->progress->finish();
      $this->progress = null;
    }
    return $this;
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
   * @return mixed
   *   The answer or <var>$default</var> if user requested no interaction or quiet execution.
   */
  final protected function ask($question, $default = null, array $autocomplete = null) {
    if ($this->input->isInteractive()) {
      $defaultDisplay = $default ? " [default: {$default}]" : null;
      return $this->getHelperSet()->get('dialog')->ask(
        $this->output,
        "<question>{$question}</question>{$defaultDisplay} ",
        $default,
        $autocomplete
      );
    }
    return $default;
  }

  /**
   * Ask to confirm.
   *
   * @param string $question
   *   The question to ask.
   * @param string $default [optional]
   *   The default answer, defaults to <code>TRUE</code>.
   * @return boolean
   *   The answer or <var>$default</var> if user requested no interaction or quiet execution.
   */
  final protected function askConfirmation($question, $default = true) {
    if ($this->input->isInteractive()) {
      $defaultDisplay = $default ? "y" : "n";
      return $this->getHelperSet()->get("dialog")->askConfirmation(
        $this->output,
        "<question>{$question}</question> [default: {$defaultDisplay}] ",
        $default
      );
    }
    return $default;
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
   * @return mixed
   *   The answer or <var>$default</var> if user requested no interaction or quiet execution.
   */
  final protected function askWithChoices($text, $default = null, array $choices = null, array $choiceExplanations = null) {
    if ($this->input->isInteractive()) {
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
    return $default;
  }

  /**
   * Change the current working directory.
   *
   * @param string $directory
   *   Canonical absolute path to the new working directory.
   * @return this
   */
  final protected function changeWorkingDirectory($directory) {
    if (getcwd() != ($directory = $this->fs->realpath($directory))) {
      $this->writeDebug("Changeing working directory to <comment>{$directory}</comment>");
      chdir($directory);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $this->input  = $input;
    $this->output = $output;
    return $this;
  }

  /**
   * Execute shell command, same as {@see Shell::execute} but output is displayed if output has verbosity level debug.
   *
   * @param string $command
   *   The command to execute.
   * @param null|string $workingDirectory [optional]
   *   Canonical absolute path to the new working directory, defaults to <code>NULL</code> (stay in current working
   *   directory).
   * @return this
   */
  final protected function exec($command, $workingDirectory = null) {
    if (isset($workingDirectory)) {
      $this->changeWorkingDirectory($workingDirectory);
    }
    $this->writeVeryVerbose($command, self::MESSAGE_TYPE_COMMENT);
    if ($this->output->getVerbosity() >= Output::VERBOSITY_DEBUG) {
      $this->getShell()->executeDisplayOutput($command);
    }
    else {
      $this->getShell()->execute($command);
    }
    return $this;
  }

  /**
   * Get a shell instance.
   *
   * @staticvar \MovLib\Core\Shell $shell
   *   Used to cache the shell.
   * @return \MovLib\Core\Shell
   *   A shell instance.
   */
  protected function getShell() {
    static $shell = null;
    if (!$shell) {
      $shell = new Shell();
    }
    return $shell;
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
  final protected function write($message, $type = null) {
    if ($this->output) {
      if (is_array($message)) {
        $message = $this->getHelper("formatter")->formatBlock($message, $type, true);
      }
      elseif ($type) {
        $message = "<{$type}>{$message}</{$type}>";
      }
      if (!$this->progress) {
        $this->output->writeln($message);
      }
    }
    return $this;
  }

  /**
   * Write message only if verbosity level is debug (highest level invoked with <code>-vvv</code>).
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @param string|array $message
   *   The message that should be displayed to the user.
   * @param string $type [optional]
   *   The message type, one of the predefined Symfony console styles (see the class constants <var>MESSAGE_TYPE_*</var>).
   *   Defaults to no style (white text).
   * @return this
   */
  final protected function writeDebug($message, $type = null) {
    if ($this->output->getVerbosity() >= Output::VERBOSITY_DEBUG) {
      $this->write($message, $type);
    }
    return $this;
  }

  /**
   * Write message only if verbosity level is verbose (lowest verbosity level invoked with <code>-v</code>).
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @param string|array $message
   *   The message that should be displayed to the user.
   * @param string $type [optional]
   *   The message type, one of the predefined Symfony console styles (see the class constants <var>MESSAGE_TYPE_*</var>).
   *   Defaults to no style (white text).
   * @return this
   */
  final protected function writeVerbose($message, $type = null) {
    if ($this->output->getVerbosity() >= Output::VERBOSITY_VERBOSE) {
      $this->write($message, $type);
    }
    return $this;
  }

  /**
   * Write message only if verbosity level is very verbose (invoked with <code>-vv</code>).
   *
   * @link http://symfony.com/doc/master/components/console/introduction.html#components-console-coloring
   * @param string|array $message
   *   The message that should be displayed to the user.
   * @param string $type [optional]
   *   The message type, one of the predefined Symfony console styles (see the class constants <var>MESSAGE_TYPE_*</var>).
   *   Defaults to no style (white text).
   * @return this
   */
  final protected function writeVeryVerbose($message, $type = null) {
    if ($this->output->getVerbosity() >= Output::VERBOSITY_VERY_VERBOSE) {
      $this->write($message, $type);
    }
    return $this;
  }

}
