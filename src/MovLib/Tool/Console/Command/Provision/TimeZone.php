<?php

/* !
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

/**
 * Set machine time zone and ensure that the time is always in sync.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TimeZone extends \MovLib\Tool\Console\Command\Provision\AbstractProvision {

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function provision() {
    global $kernel;
    $this->aptInstall([ "ntp", "tzdata" ]);
    $this->fsPutContents("/etc/timezone", $kernel->configuration->timezone, LOCK_EX, 0644, "root", "root");
    $this->fsSymlink("/usr/share/zoneinfo/{$kernel->configuration->timezone}", "/etc/localtime", true);
    $this->serviceStart("ntp");
    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function validate() {
    global $kernel;
    if (empty($kernel->configuration->timezone)) {
      throw new \LogicException("The 'timezone' must be set in the global environment configuration file");
    }
    if (file_exists("/usr/share/zoneinfo/{$kernel->configuration->timezone}") === false) {
      throw new \LogicException("The 'timezone' configuration value must contain a valid timezone");
    }
    return $this;
  }

}
