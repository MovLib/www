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
   */
  protected function provision() {
    $this->aptInstall("nodejs-legacy npm", "testing");
    if (!empty($this->config->nodejs->npm)) {
      $npm = implode(" ", (array) $this->config->nodejs->npm);
      $this->write("Installing npm packages: {$npm}");
      $this->execute("npm install --global --no-optional {$npm}");
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function validate() {
    if (isset($this->config->nodejs->npm)) {
      $this->write($this->config->nodejs->npm, true, Output::VERBOSITY_DEBUG);
      if (!empty($this->config->nodejs->npm)) {
        if (!is_string($this->config->nodejs->npm) || !is_array($this->config->nodejs->npm)) {
          throw new \InvalidArgumentException("npm packages must be given as string or array");
        }
      }
    }
    return $this;
  }

}
