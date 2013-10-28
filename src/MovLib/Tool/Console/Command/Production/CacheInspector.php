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
namespace MovLib\Tool\Console\Command\Production;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Manage various caches of the MovLib software.
 *
 * @link http://www.linuxatemyram.com/play.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CacheInspector extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("cache-inspector");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Manage various system caches.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $foundOption = false;
    foreach ($options as $option => $value) {
      $method = "empty" . ucfirst($option) . "Cache";
      if ($value === true && method_exists($this, $method)) {
        $this->{$method}();
        $foundOption = true;
      }
    }
    if ($foundOption === false) {
      $this->write("Use `movlib --help {$this->getName()}` to list all available options.", self::MESSAGE_TYPE_COMMENT);
    }
    return $options;
  }

}
