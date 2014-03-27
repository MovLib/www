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
namespace MovLib\Console\Command\Dev;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Extended cache inspector with some very special commands.
 *
 * @link http://www.linuxatemyram.com/play.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cache extends \MovLib\Console\Command\AbstractCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("cache");
    $this->setDescription("Various commands to interact with system caches.");
    $this->addOption("disk", "d", InputOption::VALUE_NONE, "Empty the server's disk cache (requires super user privileges and is only for fun).");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $foundOption = false;

    foreach ($input->getOptions() as $option => $value) {
      $method = "purge{$option}Cache";
      if ($value === true && method_exists($this, $method)) {
        $this->{$method}();
        $foundOption = true;
      }
    }

    if ($foundOption === false) {
      $this->write("Use `movlib --help {$this->getName()}` to list all available options.", self::MESSAGE_TYPE_ERROR);
    }

    return 0;
  }

  /**
   * Experimental command to purge the disk cache.
   *
   * @return this
   * @throws \RuntimeException
   */
  public function purgeDiskCache() {
    $this->checkPrivileges();
    $file = "/proc/sys/vm/drop_caches";
    if (is_file($file) === false) {
      throw new \RuntimeException("Couldn't find '{$file}'!");
    }
    $this->exec("echo 3 | tee /proc/sys/vm/drop_caches");
    $this->writeVerbose("Purged disk cache!", self::MESSAGE_TYPE_INFO);
    return $this;
  }

}
