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
namespace MovLib\Console;

/**
 * MovLib Command Line Interface Application.
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
final class Application extends \Symfony\Component\Console\Application {

  /**
   * Instantiate new MovLib CLI application.
   *
   * @global \MovLib\Core\Config $config
   */
  public function __construct() {
    global $config;

    $basename = basename($_SERVER["SCRIPT_FILENAME"], ".php");
    parent::__construct($basename, $config->version);
    cli_set_process_title($basename);

    // Create symbolic link if it doesn't exist yet for this executable, we have to make sure that the link points to
    // the symbolic link document root and not the real document root.
    $link = "/usr/local/bin/{$basename}";
    if (posix_getuid() === 0 && !is_link($link)) {
      symlink("{$config->documentRoot}/bin/{$_SERVER["SCRIPT_FILENAME"]}", $link);
    }

    $commandDirectory = ucfirst(str_replace("mov", "", $basename));
    if ($commandDirectory == "Test") {
      $commandDirectory = "Admin";
    }

    /* @var $fileinfo \SplFileInfo */
    foreach (new \RegexIterator(new \DirectoryIterator("dr://src/MovLib/Console/Command/{$commandDirectory}"), "/\.php/") as $fileinfo) {
      $command   = "\\MovLib\\Console\\Command\\{$commandDirectory}\\{$fileinfo->getBasename(".php")}";
      $reflector = new \ReflectionClass($command);
      if ($reflector->isInstantiable() && $reflector->isSubclassOf("\\Symfony\\Component\\Console\\Command\\Command")) {
        $this->add(new $command());
      }
    }
  }

}
