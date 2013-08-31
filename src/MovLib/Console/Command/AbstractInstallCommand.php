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
namespace MovLib\Console\Command;

use \MovLib\Console\Command\AbstractCommand;

/**
 * Base class for installation scripts.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractInstallCommand extends AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Absolute path to the install directory.
   *
   * @var string
   */
  const INSTALL_DIRECTORY = "/usr/local/src/";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The name of the software that's going to be installed.
   *
   * @var string
   */
  public $name;

  /**
   * The version string of the software that's going to be installed.
   *
   * @var string
   */
  public $version;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get source files via git command.
   *
   * @param string $user
   *   The GitHub username of the project owner.
   * @param string $project
   *   The GitHub projectname.
   * @return this
   */
  public function git($user, $project) {
    return $this->system("git clone git://github.com/{$user}/{$project}.git", "Could not download the source files from GitHub project: '{$user}/{$project}'");
  }

  /**
   * Get source files via wget command.
   *
   * @param string $url
   *   The absolute URL.
   * @return this
   */
  public function wget($url) {
    return $this
      ->write("Downloading source files for {$this->name} ...")
      ->system("wget --no-check-certificate {$url}", "Could not download the source files: '{$url}'")
    ;
  }

  /**
   * Extract tar-archive and delete it after successful extraction.
   *
   * @param string $path
   *   The absolute path to the archive.
   * @return this
   */
  public function tar($path) {
    return $this
      ->write("Extracting source files archive for {$this->name} ...")
      ->system("tar xzf {$path}", "Could not extract archive: '{$path}'")
      ->write("Removing source files archive of {$this->name} ...")
      ->system("rm -f {$path}", "Could not delete source archive: '{$path}'")
    ;
  }

  /**
   * Uninstall old installation.
   *
   * @return this
   */
  public function uninstall() {
    $this->write("Uninstalling old {$this->name} installation.");
    if ($this->exec("dpkg -s {$this->name}", "No old installation found.", [ "exit_on_error" => false, "return_status" => true ]) === 0) {
      $this->system("dpkg -r {$this->name}", "Could not uninstall old installation.");
    }
    if ($this->askConfirmation("Remove old source files?", false) === true) {
      exec("rm -rf " . self::INSTALL_DIRECTORY . "{$this->name}-*");
    }
    return $this;
  }

  /**
   * Call the configure script.
   *
   * @param array $options
   *   Array containing the configure options.
   * @return this
   */
  public function configureInstallation(array $options = []) {
    return $this
      ->write("Configuring installation of {$this->name} ...")
      ->system("./configure " . implode(" ", $options), "Could not configure {$this->name}")
    ;
  }

  /**
   * Install the software.
   *
   * @todo Failure of some calls are not fatal and should not abort execution.
   * @return this
   */
  public function install() {
    if ($this
      ->write("Starting installation of {$this->name} ...")
      ->system("make", "Could not 'make' {$this->name}")
      ->system("checkinstall make install", "Could not 'checkinstall make install' {$this->name}")
      ->system("make clean", "Could not 'make clean' {$this->name}")
      ->system("ldconfig", "Could not 'ldconfig' {$this->name}", [ "return_status" => true ]) === 0
    ) {
      $this->write("Successfully installed {$this->name}-{$this->version}");
    }
    return $this;
  }

}
