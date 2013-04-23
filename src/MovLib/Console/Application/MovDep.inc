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
namespace MovLib\Console\Application;

use Symfony\Component\Console\Application;

/**
 * Main console application for adminsitrative tasks.
 *
 * The console application is used to run several administrative tasks. The MovLib software does not have any
 * administrative backend, instead all such tasks are handled with console applications.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovDep extends Application {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('MovDep', '0.0.1-dev');
    foreach ([ 'Nginx', 'Routes' ] as $cmd) {
      $cmd = 'MovLib\\Console\\Command\\' . $cmd;
      $this->add(new $cmd());
      $this->run();
    }
  }

}
