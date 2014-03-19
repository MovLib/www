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

use \MovLib\Data\FileSystem;

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

  /**
   * Instantiate new MovLib CLI application.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $name
   *   The application's name.
   * @param string $version
   *   The application's version.
   * @param string $directoryName
   *   The directory from which commands should be imported.
   * @throws \LogicException
   */
  public function __construct($name, $version, $directoryName) {
    global $kernel;
    parent::__construct($name, $version);

    // Create symbolic link if it doesn't exist yet for this executable.
    if (posix_getuid() === 0) {
      $name   = "/bin/mov" . strtolower($directoryName);
      $target = "/usr/local{$name}";
      if (is_link($target) === false) {
        FileSystem::createSymbolicLink("{$kernel->documentRoot}{$name}.php", $target);
      }
    }

    // Build the canonical absolute path to the directory where the commands reside.
    $directory = "{$kernel->documentRoot}/src/MovLib/Tool/Console/Command/{$directoryName}";

    // Make that the desired directory actually exists.
    if (is_dir($directory) === false) {
      throw new \LogicException("Directory '{$directory}' doesn't exist");
    }

    // Go through all files in the directory and add command instance to our application if possible.
    /* @var $file \splFileInfo */
    foreach (new \DirectoryIterator("glob://{$directory}/*.php") as $file) {
      $basename  = $file->getBasename(".php");
      $class     = "\\MovLib\\Tool\\Console\\Command\\{$directoryName}\\{$basename}";
      $reflector = new \ReflectionClass($class);
      if ($reflector->isInstantiable() && $reflector->isSubclassOf("\\Symfony\\Component\\Console\\Command\\Command")) {
        $this->add(new $class());
      }
    }
  }

}
