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

/**
 * Install Oracle Java JDK.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Java extends \MovLib\Tool\Console\Command\Provision\AbstractProvision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * debconf set selection preseed data for unattended Java 7 installation.
   *
   * @var string
   */
  protected $java7preseed = <<<EOT
oracle-java7-installer	oracle-java7-installer/local	string
oracle-java7-installer	shared/present-oracle-license-v1-1	note
oracle-java7-installer	shared/error-oracle-license-v1-1	error
oracle-java7-installer	shared/accepted-oracle-license-v1-1	boolean	true
oracle-java7-installer	oracle-java7-installer/not_exist	error
EOT;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function provision() {
    global $kernel;

    // We don't want to write this over and over again.
    $package = "oracle-java{$kernel->configuration->java->version}-";

    // Purge any other installed Java versions.
    exec("aptitude search 'openjdk*' | grep '^i'", $output);
    if (!empty($output)) {
      foreach ($output as $delta => $line) {
        $output[$delta] = preg_replace("/^i\s+([a-z0-9-_]+).*/i", "$1", $line);
      }
      $this->aptPurge($output);
    }

    // Add the WebUpd8 source to apt.
    $this->aptSource(
      "http://ppa.launchpad.net/webupd8team/java/ubuntu",
      $kernel->configuration->java->release,
      "main",
      "EEA14886"
    );

    // Accept the Oracle licenses for Java if we haven't done so already.
    if (isset($this->{"java{$kernel->configuration->java->version}preseed"})) {
      $this->aptPreseed($this->{"java{$kernel->configuration->java->version}preseed"}, "oracle");
    }
    else {
      throw new \LogicException("No preseed available for Java {$kernel->configuration->java->version}");
    }

    // Finally install the desired version.
    $this->aptInstall([ "{$package}installer", "{$package}set-default" ]);

    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function validate() {
    global $kernel;

    foreach ([ "version", "release" ] as $option) {
      if (empty($kernel->configuration->java->{$option})) {
        throw new \LogicExcepiton("The Java '{$option}' cannot be empty");
      }
    }

    if ($kernel->configuration->java->version < 6 || $kernel->configuration->java->version > 8) {
      throw new \LogicException("The Java 'version' must be set, valid values are: 6, 7, 8");
    }

    if (!isset($kernel->configuration->java->release)) {
      throw new \LogicException("The Java 'release' must be set");
    }

    return $this;
  }

}
