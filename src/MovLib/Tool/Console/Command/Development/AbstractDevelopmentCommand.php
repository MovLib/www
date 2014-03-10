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
namespace MovLib\Tool\Console\Command\Development;

/**
 * Base class for all development commands.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractDevelopmentCommand extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * Instantiate new development CLI command.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $name
   *   The command name.
   * @throws \DomainException
   */
  public function __construct($name = null) {
    global $kernel;
    if ($kernel->production === true) {
      throw new \DomainException("This command is only available in development environments!");
    }
    parent::__construct($name);
  }

}
