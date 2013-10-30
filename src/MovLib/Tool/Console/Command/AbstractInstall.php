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
namespace MovLib\Tool\Console\Command;

use \MovLib\Data\UnixShell as sh;
use \MovLib\Exception\ConsoleException;

/**
 * Base class for installation scripts.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractInstall extends \MovLib\Tool\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Absolute path to the install directory.
   *
   * @var string
   */
  const SOURCE_DIRECTORY = "/usr/local/src";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The name of the program that's going to be installed.
   *
   * @var string
   */
  private $installationName;

  /**
   * The version string of the software that's going to be installed.
   *
   * @var string
   */
  private $version;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new CLI installation command.
   *
   * @param string $name
   *   The command name.
   * @param string $installationName
   *   The name of the installation program.
   * @throws \InvalidArgumentException
   */
  public function __construct($name, $installationName) {
    parent::__construct($name);
    $this->setInstallationName($installationName);
    $this->setAliases([]); // Install commands have no aliases!
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Call the configure script.
   *
   * @param array $options
   *   Array containing the configure options.
   * @param string $flags
   *   Flags to set before configuration, e.g. <i>CFLAGS</i>.
   * @return this
   * @throws \MovLib\Exception\ConsoleException
   */
  protected function configureInstallation(array $options = [], $flags = null) {
    $this->write("Configuring installation of {$this->installationName} ...");
    if (!empty($flags)) {
      $flags .= " ";
    }
    if (sh::executeDisplayOutput("{$flags}./configure " . implode(" ", $options)) === false) {
      throw new ConsoleException("Couldn't configure {$this->installationName}");
    }
    return $this;
  }

  /**
   * Get the name of the installation program.
   *
   * @return string
   *   The name of the installation program.
   */
  public function getInstallationName() {
    return $this->installationName;
  }

  /**
   * Get the version.
   *
   * @return string
   *   The version.
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Get source files via git command.
   *
   * @param string $user
   *   The GitHub username of the project owner.
   * @param string $project
   *   The GitHub projectname.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \MovLib\Exception\ConsoleException
   */
  protected function git($user, $project) {
    if (!is_string($user) || empty($user) || !is_string($project) || empty($project)) {
      throw new \InvalidArgumentException("User and project must be set to clone a GitHub repository.");
    }
    $this->write("Cloning repository {$user}/{$project} ...", self::MESSAGE_TYPE_COMMENT);
    if (sh::executeDisplayOutput("git clone git://github.com/{$user}/{$project}.git") === false) {
      throw new ConsoleException("Couldn't clone GitHub repository: '{$user}/{$project}'");
    }
    return $this;
  }

  /**
   * Install the software.
   *
   * @return this
   * @throws \MovLib\Exception\ConsoleException
   */
  protected function install() {
    $this->write("Starting installation of {$this->installationName} ...");
    if (sh::executedisplayoutput("make") === false) {
      throw new ConsoleException("Couldn't 'make' {$this->installationName}!");
    }
    if (sh::executedisplayoutput("checkinstall make install") === false) {
      throw new ConsoleException("Couldn't 'checkinstall make install' {$this->installationName}!");
    }
    sh::executeDisplayOutput("make test");
    sh::executedisplayoutput("make clean");
    sh::executedisplayoutput("ldconfig");
    $this->write("Successfully installed {$this->installationName}-{$this->version}");
    return $this;
  }

  /**
   * Set the name of the installation program.
   *
   * @param string $installationName
   *   The name of the installation program.
   * @return this
   * @throws \InvalidArgumentException
   */
  protected function setInstallationName($installationName) {
    if (!is_string($installationName) || empty($installationName)) {
      throw new \InvalidArgumentException("Installation name must be of type string and not empty!");
    }
    $this->installationName = $installationName;
    return $this;
  }

  /**
   * Set the version.
   *
   * @param string $version
   *   The version to set.
   * @return this
   * @throws \InvalidArgumentException
   */
  public function setVersion($version) {
    if (!is_string($version) || empty($version)) {
      throw new \InvalidArgumentException("Version must be of type string and not empty!");
    }
    $this->version = $version;
    return $this;
  }

  /**
   * Extract tar-archive and delete it after successful extraction.
   *
   * @param string $path
   *   The absolute path to the archive.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \MovLib\Exception\ConsoleException
   */
  protected function tar($path) {
    if (!is_string($path) || !is_dir($path)) {
      throw new \InvalidArgumentException("The given path isn't valid!");
    }
    $this->write("Extracting source files archive for {$this->installationName} ...");
    if (sh::executedisplayoutput("tar xzf {$path}") === false) {
      throw new ConsoleException("Couldn't extract tar archive: '{$path}'");
    }
    $this->write("Removing source files archive of {$this->installationName} ...");
    if (sh::execute("rm -f {$path}") === false) {
      throw new ConsoleException("Couldn't delete tar archive after extraction: '{$path}'");
    }
    return $this;
  }

  /**
   * Uninstall old installation.
   *
   * @return this
   * @throws \MovLib\Exception\ConsoleException
   */
  protected function uninstall() {
    $this->write("Uninstalling old {$this->installationName} installation.");
    if (sh::execute("dpkg -s {$this->installationName}") === true && sh::executedisplayoutput("dpkg -r {$this->installationName}") === false) {
      throw new ConsoleException("Couldn't uninstall old installation.");
    }
    if ($this->askConfirmation("Remove old source files?", false) === true && sh::execute("rm -rf " . self::SOURCE_DIRECTORY . "{$this->installationName}-*") === false) {
      throw new ConsoleException("Couldn't remove source files.");
    }
    return $this;
  }

  /**
   * Get source files via wget command.
   *
   * @param string $url
   *   The absolute URL.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \MovLib\Exception\ConsoleException
   */
  protected function wget($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED)) {
      throw new \InvalidArgumentException("The given URL isn't valid!");
    }
    $this->write("Downloading source files for {$this->installationName} ...", self::MESSAGE_TYPE_COMMENT);
    if (sh::executedisplayoutput("wget --no-check-certificate {$url}") === false) {
      throw new ConsoleException("Couldn't download the source files: '{$url}'");
    }
    return $this;
  }

}
