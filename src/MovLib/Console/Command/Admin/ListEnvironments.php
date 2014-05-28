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
namespace MovLib\Console\Command\Admin;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * List all available environments.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ListEnvironments extends \MovLib\Console\Command\AbstractCommand {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "ListEnvironments";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("list-environments");
    $this->setDescription("List all available environments.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->write("<info>Available environments:</info>\n\n");
    /* @var $fileinfo \SplFileInfo */
    foreach (new \RegexIterator(new \DirectoryIterator("dr://src/MovLib/Core/Config"), "/\.php$/") as $fileinfo) {
      $output->write("  - {$fileinfo->getBasename(".php")}");
    }
    $output->write("\n\n<comment>NOTE</comment> that all environments are merged with the dist environment configuration.\n\n");
    return 0;
  }

}
