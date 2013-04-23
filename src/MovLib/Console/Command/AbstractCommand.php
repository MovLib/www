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

use Symfony\Component\Console\Command\Command;

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
   * Display error message, rollback everything and exit program.
   *
   * @param string $message
   *   The message that should be displayed to the user.
   * @return void
   */
  protected function exitOnError($message) {
    $this->writeError([ 'ERROR!', $message . '! Rolling back any changes and exiting.' ]);
    $this->rollback();
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
  protected function write($message, $type) {
    if (is_array($message)) {
      /* @var $formatter Symfony\Component\Console\Helper\FormatterHelper */
      $formatter = $this->getHelper('formatter');
      $message = $formatter->formatBlock($message, $type, TRUE);
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
  protected function writeInfo($infoMessage) {
    return $this->write($infoMessage, 'info');
  }

  /**
   * Display comment message (yellow text color).
   *
   * @param string|array $commentMessage
   *   The message that should be displayed to the user.
   * @return \Symfony\Component\Console\Command\Command
   */
  protected function writeComment($commentMessage) {
    return $this->write($commentMessage, 'comment');
  }

  /**
   * Display question message (black text color on blue background).
   *
   * @param string|array $questionMessage
   *   The message that should be displayed to the user.
   * @return \Symfony\Component\Console\Command\Command
   */
  protected function writeQuestion($questionMessage) {
    return $this->write($questionMessage, 'question');
  }

  /**
   * Display error message (black text color on red background).
   *
   * @param string|array $errorMessage
   *   The message that should be displayed to the user.
   * @return \MovLib\Console\Command\AbstractCommand
   */
  protected function writeError($errorMessage) {
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

}
