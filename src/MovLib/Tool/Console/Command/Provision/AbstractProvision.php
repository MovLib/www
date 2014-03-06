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

use \MovLib\Tool\Console\Command\Production\Provision;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Output\Output;

/**
 * Every concrete provision class has to implement the provision interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractProvision {
  use \MovLib\Data\TraitShell;
  use \MovLib\Data\TraitFileSystem;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The configuration array.
   *
   * Have a look at <code>conf/env.ini</code>.
   *
   * @var object
   */
  protected $config;

  /**
   * The environment we are currently working under.
   *
   * @var string
   */
  protected $environment;

  /**
   * Whether to ignore versions while installing or not.
   *
   * @var boolean
   */
  protected $force;

  /**
   * Output instance.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  private $output;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new provisioner.
   *
   * @param object $config
   *   The global environment configuration.
   * @param string $environment
   *   The environment we are provisioning for.
   * @param boolean $force
   *   Whether to ignore already installed software or not.
   * @param \Symfony\Component\Console\Output\OutputInterface $output [optional]
   *   Output instance, defaults to no output which basically means that the script won't output anything.
   */
  public function __construct($config, $environment, $force, OutputInterface $output = null) {
    $this->shellThrowExceptions = true;
    $this->config               = $config;
    $this->environment          = $environment;
    $this->force                = $force;
    $this->output               = $output;
    $this->validate()->provision();
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Provision the software.
   *
   * @return $this
   */
  abstract protected function provision();

  /**
   * Validate the configuration value(s).
   *
   * @return this
   */
  abstract protected function validate();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Execute apt-get.
   *
   * @param string $arguments
   *   The arguments that should be passed to apt-get.
   * @return $this
   * @throws \RuntimeException
   */
  private function apt($arguments) {
    $this->execute("DEBIAN_FRONTEND=noninteractive && apt-get {$arguments}");
    return $this;
  }

  /**
   * Install new package.
   *
   * @param string $package
   *   The name of the package.
   * @param string $release [optional]
   *   The target release (e.g. <code>"unstable"</code>).
   * @return $this
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function aptInstall($package, $release = null) {
    // Validate the type of the given package parameter.
    if (!is_string($package) && !is_array($package)) {
      throw new \InvalidArgumentException("\$package must be of type string or array");
    }

    // Make sure that the package isn't empty.
    if (empty($package)) {
      throw new \InvalidArgumentException("\$package cannot be empty");
    }

    // Put the packages together to a string if it is an array.
    $package = implode(" ", (array) $package);

    // Add the target release option if a release was given.
    if ($release) {
      $release = " --target-release {$release}";
    }

    // Install the desired package(s).
    return $this->aptUpdate()->write("Installing {$package}...")->apt("install --yes{$release} {$package}");
  }

  /**
   * Perform apt-get update (and implicit safe upgrade).
   *
   * @staticvar boolean $executed
   *   Used to ensure that we aren't running too many updates.
   * @param boolean $force [optional]
   *   Force update no matter if it was just executed or not.
   * @return $this
   * @throws \RuntimeException
   */
  private function aptUpdate($force = false) {
    static $executed = false;
    if ($force === true || $executed === false) {
      $this->write("Updating and upgrading apt packages...");
      $this->apt("update")->apt("upgrade --yes");
    }
    return $this;
  }

  /**
   * Writes a message to the output.
   *
   * @param string|array $messages
   *   The message as an array of lines or a single string.
   * @param integer $level [optional]
   *   The message is only written if the verbosity level is higher or equal to the given level, defaults to
   *   <var>Output::VERBOSITY_VERBOSE</var>.
   * @param boolean $newline [optional]
   *   Whether to add a newline, defaults to <code>TRUE</code> (add newline).
   * @param integer $type [optional]
   *   The type of output, defaults to <var>Output::OUTPUT_NORMAL</var>.
   * @return $this
   */
  protected function write($messages, $level = Output::VERBOSITY_VERBOSE, $newline = true, $type = Output::OUTPUT_NORMAL) {
    if ($this->output && $this->output->getVerbosity() >= $level) {
      $this->output->write($messages, $newline, $type);
    }
    return $this;
  }

}
