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
namespace MovDev\Console\Application;

use \ReflectionClass;
use \Symfony\Component\Console\Application;

/**
 * MovDev Command Line Interface
 *
 * The MovDev command line interface is a Symfony2 Console Application and combines all possible MovDev Symfony2 Console
 * Commands for easy execution. The CLI is used to run several development related tasks.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovDev extends Application {

  /**
   * @inheritdoc
   */
  public function __construct(){
    global $config;
    parent::__construct("MovDev", $GLOBALS["movlib"]["version"]);
    if (!$config) {
      $config = new \MovLib\Tool\Configuration();
    }
    foreach (glob("{$config->documentRoot}/src/MovDev/Console/Command/*.php") as $command) {
      $command = "\\MovDev\\Console\\Command\\" . basename($command, ".php");
      $reflectionClass = new ReflectionClass($command);
      // Make sure we do not include any abstract classes or interfaces.
      if ($reflectionClass->isInstantiable()) {
        $this->add(new $command());
      }
    }
  }

}
