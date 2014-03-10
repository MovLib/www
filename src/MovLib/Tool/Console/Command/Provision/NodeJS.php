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
namespace MovLib\Tool\Console\Command\Provision;

use \Symfony\Component\Console\Output\Output;

/**
 * Install nodejs and global npm modules.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class NodeJS extends \MovLib\Tool\Console\Command\Provision\AbstractProvision {

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function provision() {
    global $kernel;
    $this->aptInstall("nodejs-legacy npm", "testing");
    if (!empty($kernel->configuration->nodejs->npm)) {
      $npm = implode(" ", (array) $kernel->configuration->nodejs->npm);
      $this->write("Installing npm packages: {$npm}");
      $this->shellExecute("npm install --global --no-optional {$npm}");
    }
    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function validate() {
    global $kernel;
    if (isset($kernel->configuration->nodejs->npm)) {
      $this->write($kernel->configuration->nodejs->npm, true, Output::VERBOSITY_DEBUG);
      if (!empty($kernel->configuration->nodejs->npm)) {
        if (!is_string($kernel->configuration->nodejs->npm) || !is_array($kernel->configuration->nodejs->npm)) {
          throw new \LogicException("npm packages must be given as string or array");
        }
      }
    }
    return $this;
  }

}
