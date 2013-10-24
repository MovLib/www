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
namespace MovLib\Tool\Console;

use \ReflectionClass;

/**
 * MovLib Command Line Interface
 *
 * The MovLib command line interface is a Symfony2 Console Application and combines all possible MovLib Symfony2 Console
 * Commands for easy execution. The CLI is used to run several administrative tasks. The MovLib software does not have
 * any administrative backend, instead all such tasks are handled with console applications.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Application extends \Symfony\Component\Console\Application {
  use \MovLib\Tool\TraitUtilities;

  /**
   * Instantiate new MovLib CLI application.
   *
   * @global \MovLib\Tool\Configuration $config
   */
  public function __construct() {
    global $config;
    parent::__construct("MovLib Command Line Interface (CLI)", $config->version);
    $this->importCommands("Production");
    if ($config->production === false) {
      $this->importCommands("Development");
    }
  }

  /**
   * Imports all instantiable commands from the given directory (subnamespace directory of Command).
   *
   * @param string $directory
   *   The directory name.
   * @return this
   */
  protected function importCommands($directory) {
    foreach (glob(__DIR__ . "/Command/{$directory}/*.php") as $command) {
      $command = "\\MovLib\\Tool\\Console\\Command\\{$directory}\\" . basename($command, ".php");
      // Make sure we don't include any abstract classes or interfaces.
      if ((new ReflectionClass($command))->isInstantiable()) {
        $this->add(new $command());
      }
    }
    return $this;
  }

}
