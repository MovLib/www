<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2014-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Console\Command\Install;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Help related tasks.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Help extends \MovLib\Console\Command\AbstractCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("seed-help");
    $this->setDescription("Perform various help related tasks.");
    $this->addArgument("task", InputArgument::OPTIONAL, "generate or save");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Array containing the names of all tasks that could be executed.
    $tasks = [
      "generate",
      "save",
    ];

    if ($task = $input->getArgument('task')) {
      if (method_exists($this, $task)) {
        $this->$task();
      }
      else {
        $this->write("There is no task called '{$task}'.", self::MESSAGE_TYPE_ERROR);
      }
    }
    else {
      $this->write("No task given", self::MESSAGE_TYPE_ERROR);
    }

    return 0;
  }

  protected function generate() {
    $this->write("Generate..", self::MESSAGE_TYPE_INFO);
  }

  protected function save() {
    $this->write("Saving..", self::MESSAGE_TYPE_INFO);
  }

}
