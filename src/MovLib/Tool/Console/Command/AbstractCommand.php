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
namespace MovLib\Tool\Console\Command;

use \Symfony\Component\Console\Input\InputInterface;
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
   * <code>FALSE</code> if the user requested no interaction, otherwise <code>TRUE</code>.
   *
   * @var boolean
   */
  protected $interaction = true;

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
   * <code>TRUE</code> if the user requested no output, otherwise <code>FALSE</code>.
   *
   * Default is true, this allows us to use our commands in other classes and not only via console.
   *
   * @var boolean
   */
  protected $quiet = true;


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


  // ------------------------------------------------------------------------------------------------------------------- Magic Property Getters


  /**
   * Starts the progress output.
   *
   * @param type $max
   */
  protected final function progressStart($max = null) {
    if ($this->output && $this->quiet === false) {
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
  protected final function progressAdvance($steps = 1, $redraw = false) {
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
  protected final function progressFinish() {
    if ($this->progress) {
      $this->progress->finish();
      $this->progress = null;
    }
    return $this;
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
  protected final function addInputOption($name, $mode = null, $description = "", $default = null) {
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
   * @return mixed
   *   The answer or <var>$default</var> if user requested no interaction or quiet execution.
   */
  protected final function ask($question, $default = null, array $autocomplete = null) {
    if ($this->output && $this->dialog && $this->interaction === true && $this->quiet === false) {
      $defaultDisplay = $default ? " [default: {$default}]" : null;
      return $this->dialog->ask($this->output, "<question>{$question}</question>{$defaultDisplay} ", $default, $autocomplete);
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
  protected final function askConfirmation($question, $default = true) {
    if ($this->output && $this->dialog && $this->interaction === true && $this->quiet === false) {
      $defaultDisplay = $default ? "y" : "n";
      return $this->dialog->askConfirmation($this->output, "<question>{$question}</question> [default: {$defaultDisplay}] ", $default);
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
  protected final function askWithChoices($text, $default = null, array $choices = null, array $choiceExplanations = null) {
    if ($this->output && $this->interaction === true && $this->quiet === false) {
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
   * Check if the executing user has root privileges.
   *
   * @param boolean $exception [optional]
   *   Flag indicating if the method should throw an exception or return <code>TRUE</code>/<code>FALSE</code>.
   *   Defaults to throwing an exception.
   * @return this|boolean
   *   Depends on <var>$exception</var>.
   * @throws \RuntimeException
   */
  protected final function checkPrivileges($exception = true) {
    if (posix_getuid() !== 0) {
      if ($exception === true) {
        throw new \RuntimeException("This script must be executed as privileged user (root or sudo).");
      }
      else {
        return false;
      }
    }
    if ($exception === false) {
      return true;
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
   * @return array
   *   The input options array.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    /* @var $helperSet \Symfony\Component\Console\Helper\HelperSet */
    if (($helperSet = $this->getHelperSet()) && $helperSet->has("dialog")) {
      $this->dialog = $helperSet->get("dialog");
    }
    $this->input  = $input;
    $this->output = $output;
    if (($options = $input->getOptions())) {
      if (isset($options["no-interaction"])) {
        $this->interaction = !$options["no-interaction"];
      }
      if (isset($options["quiet"])) {
        $this->quiet = $options["quiet"];
      }
    }
    $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG); // Always display exceptions!
    return $options;
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
      throw new \InvalidArgumentException("Name cannot be empty!");
    }
    $shortcut = null;
    foreach (explode("-", $name) as $namePart) {
      $shortcut .= $namePart[0];
    }
    return $shortcut;
  }

  /**
   * Recursive glob that finds all php files in the given directory.
   *
   * @param string $path
   *   Absolut path to glob.
   * @param callable $callback
   *   Callable to call on each iteration.
   * @param string|array $extension [optional]
   *   The extension of files to search for, either as string containing a single extension or a numeric array with
   *   multiple extensions, defaults to <code>"php"</code>.
   * @return this
   */
  protected function globRecursive($path, $callback, $extension = "php") {
    if ($extension !== (array) $extension) {
      $extension = [ $extension ];
    }

    /* @var $splFileInfo \SplFileInfo */
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST) as $splFileInfo) {
      if ($splFileInfo->isFile() && in_array($splFileInfo->getExtension(), $extension)) {
        call_user_func($callback, $splFileInfo);
      }
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
    if ($this->output && $this->quiet === false) {
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

}
