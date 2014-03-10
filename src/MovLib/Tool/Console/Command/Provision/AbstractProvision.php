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
  use \MovLib\Data\TraitFileSystem;


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * @param boolean $force
   *   Whether to ignore already installed software or not.
   * @param \Symfony\Component\Console\Output\OutputInterface $output [optional]
   *   Output instance, defaults to no output which basically means that the script won't output anything.
   */
  public function __construct($force, OutputInterface $output = null) {
    $this->shellThrowExceptions = true;
    $this->force                = $force;
    $this->output               = $output;
    $this->validate();
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Provision the software.
   *
   * @return $this
   */
  abstract public function provision();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Execute apt-get.
   *
   * @param string $arguments
   *   The arguments that should be passed to apt-get.
   * @return $this
   * @throws \RuntimeException
   */
  final private function apt($arguments) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($arguments)) {
      throw new \InvalidArgumentException("\$arguments cannot be empty");
    }
    if (is_string($arguments) === false) {
      throw new \InvalidArgumentException("\$arguments must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->shellExecute("DEBIAN_FRONTEND=noninteractive && apt-get {$arguments}");
    return $this;
  }

  /**
   * Install new package.
   *
   * @param string|array $package
   *   The name(s) of the package(s).
   * @param string $release [optional]
   *   The target release (e.g. <code>"unstable"</code>).
   * @return $this
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  final protected function aptInstall($package, $release = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (is_string($package) === false && is_array($package) === false) {
      throw new \InvalidArgumentException("\$package must be of type string or array");
    }
    if (empty($package)) {
      throw new \InvalidArgumentException("\$package cannot be empty");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $package = implode(" ", (array) $package);
    if ($release) {
      $release = " --target-release {$release}";
    }
    return $this->aptUpdate()->write("Installing {$package}...")->apt("install --yes{$release} {$package}");
  }

  /**
   * Preseed answers for unattended package installation.
   *
   * @param string $answers
   *   The answers for the questions in <code>devconf-set-selections</code> format.
   * @param string $search
   *   A search key that is common for all answers.
   * @return $this
   * @throws \Exception
   * @throws \RuntimeException
   */
  final protected function aptPreseed($answers, $search) {
    try {
      $this->shExecute("debconf-get-selections | grep {$search}");
    }
    catch (\RuntimeException $e) {
      foreach (explode("\n", $answers) as $answer) {
        $this->shExecute("echo '{$answer}' | debconf-set-selections");
      }
    }
    return $this;
  }

  /**
   * Purge software from the system including all it's dependencies that were auto-installed.
   *
   * <b>Note:</b> This will silently continue if the software isn't installed.
   *
   * @param string|array $package
   *   The name(s) of the package(s).
   * @return $this
   */
  final protected function aptPurge($package) {
    $package = implode(" ", (array) $package);
    exec("DEBIAN_FRONTEND=noninteractive && apt-get purge --yes {$package} && apt-get autoremove --purge --yes");
    return $this;
  }

  /**
   * Create new apt source.
   *
   * @param type $location
   * @param type $release
   * @param type $repos
   * @param type $key
   * @param type $keyServer
   * @return $this
   */
  final protected function aptSource($location, $release = "stable", $repos = "main", $key = null, $keyServer = "keyserver.ubuntu.com") {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($location)) {
      throw new \InvalidArgumentException("\$location cannot be empty");
    }
    foreach ([ "location", "release", "repos", "key", "keyServer" ] as $param) {
      if (isset(${$param}) && is_string(${$param}) === false) {
        throw new \InvalidArgumentException("\${$param} must be of type string");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $hostname = parse_url($location, PHP_URL_HOST);
    $source   = "/etc/apt/sources.d/{$hostname}.list";

    if (file_exists($source) === false) {
      $this->fsPutContents($source, "# {$source}\n\ndeb {$location} {$release} {$repos}\n");
      if ($key) {
        $this->shellExecute("DEBIAN_FRONTEND=noninteractive && apt-key adv --keyserver {$keyServer} --recv-keys {$key}");
      }
      $this->aptUpdate(true);
    }

    return $this;
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
  final private function aptUpdate($force = false) {
    static $executed = false;

    // @devStart
    // @codeCoverageIngoreStart
    if (is_bool($force) === false) {
      throw new \InvalidArgumentExcepiton("\$force must be of type boolean");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    if ($force === true || $executed === false) {
      $this->write("Updating and upgrading apt packages...");
      $this->apt("update")->apt("upgrade --yes");
    }

    return $this;
  }

  /**
   * Install package with checkinstall command.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $path
   *   Absolute path to the source that should be configured.
   * @param string $pkgName
   *   The name of the package (<code>"movlib-"</code> is automatically added to the beginning of the name).
   * @param string $pkgVersion
   *   The verion of the software to be installed.
   * @param array $arguments [optional]
   *   Additional arguments that should be passed to <code>checkinstall</code> as numeric array. See <code>man
   *   checkinstall</code> for more information.
   * @return $this
   * @throws \RuntimeException
   */
  final protected function checkinstall($path, $pkgName, $pkgVersion, array $arguments = []) {
    global $kernel;
    if (chdir($path) === false) {
      throw new \RuntimeException("Couldn't change to directory '{$path}'");
    }
    $this->shExecute("checkinstall --" . implode(" --", array_merge([
      "default",
      "install",
      "maintainer='{$kernel->configuration->webmaster}'",
      "nodoc",
      "pkgname='movlib-{$pkgName}'",
      "pkgversion='{$pkgVersion}'",
      "type='debian'",
    ], $arguments)));
    return $this;
  }

  /**
   * Configure given source.
   *
   * @param string $path
   *   Absolute path to the source that should be configured.
   * @param \MovLib\Stub\Configuration\Configure $arguments [optional]
   *   The configure arguments for the source, defaults to <code>NULL</code> no special arguments.
   * @return $this
   * @throws \RuntimeException
   */
  final protected function configure($path, $arguments = null) {
    $configureCommand = null;
    if (empty($arguments->cflags)) {
      $configureCommand .= "CFLAGS='-O3 -m64 -march=native' ";
    }
    else {
      $configureCommand .= $arguments->cflags;
    }
    if (empty($arguments->cxxflags)) {
      $configureCommand .= "CXXFLAGS='-O3 -m64 -march=native CPPFLAGS='-O3 -m64 -march=native'";
    }
    if (!empty($arguments->ldflags)) {
      $configureCommand .= "LDFLAGS='{$arguments->ldflags}' ";
    }
    $configureCommand .= "./configure";
    if (!empty($arguments->arguments)) {
      $configureCommand .= " --" . implode(" --", $arguments->arguments);
    }
    if (chdir($path) === false) {
      throw new \RuntimeException("Couldn't change to directory '{$path}'");
    }
    $this->shExecute($configureCommand);
    return $this;
  }

  /**
   * Get requirements for this software.
   *
   * @return null|array
   *   Either <code>NULL</code> (default) which means this software has no dependencies or an array containing all
   *   dependencies.
   */
  public function dependencies() {
    // Defaults to NULL
  }

  /**
   * Download given URL to the temporary directory.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $url
   *   The URL of the file(s) or the Git repository to download.
   * @param string $filename [optional]
   *   The desired output filename. If <code>NULL</code> is passed (default) the basename of the URL is used as filename.
   * return string
   *   Absolute path to the downloaded file.
   * @throws \RuntimeException
   */
  final protected function download($url, $filename = null) {
    global $kernel;
    if (!$filename) {
      $filename = basename($url);
    }
    $filename = "{$kernel->configuration->directory->tmp}/{$filename}";
    $scheme   = parse_url($url, PHP_URL_SCHEME);
    if (file_exists($filename) === false) {
      switch ($scheme) {
        case "ftp":
        case "http":
        case "https":
          $this->downloadWeb($url, $filename);
          break;

        case "git":
          $this->downloadGit($url, $filename);
          break;

        default:
          throw new \LogicException("Unknown scheme {$scheme}");
      }
    }
    $this->shExecute("chown --recursive root:root '{$filename}'");
    return $filename;
  }

  /**
   * Clone given Git repository.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $url
   *   The URL of the Git repository to download.
   * @param string $filename [optional]
   *   The desired output filename. If <code>NULL</code> is passed (default) the basename of the URL is used as filename.
   * @return $this
   * @throws \RuntimeExcepiton
   */
  final private function downloadGit($url, $filename) {
    try {
      $this->shExecute("which git");
    }
    catch (\RuntimeException $e) {
      $this->aptInstall("git");
    }
    $this->shExecute("git clone '{$url}' '{$filename}'");
    return $this;
  }

  /**
   * Download given URL to the temporary directory.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $url
   *   The URL of the file(s) or the Git repository to download.
   * @param string $filename [optional]
   *   The desired output filename. If <code>NULL</code> is passed (default) the basename of the URL is used as filename.
   * @return $this
   * @throws \RuntimeException
   */
  final private function downloadWeb($url, $filename) {
    $source = fopen($url, "rb");
    if ($source === false) {
      throw new \RuntimeException("Couldn't start download of '{$url}'");
    }
    $target = fopen($filename, "wb");
    while (feof($source) !== false) {
      if (($chunk = fread($source, 8192)) === false) {
        throw new \RuntimeException("Couldn't download next chunk from '{$url}'");
      }
      fwrite($target, $chunk, 8192);
    }
    if (fclose($source) === false || fclose($target)) {
      throw new \RuntimeExcepiton("Couldn't close file handles of '{$url}' and/or '{$filename}'");
    }
    return $this;
  }

  /**
   * Extract given source archive to target directory.
   *
   * @param string $source
   *   Absolute path to the source archive.
   * @param string $target
   *   Absolute path to the target directory.
   * @return $this
   * @throws \BadMethodCallException
   * @throws \PharException
   * @throws \UnexpectedValueException
   */
  final protected function extract($source, $target) {
    $archive = new \PharData($source);
    $archive->extractTo($target);
    unset($archive);
    $this->shExecute("chown --recursive --silent root:root '{$target}'");
    return $this;
  }

  /**
   * Get the absolute path to the source files.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $name
   *   The name of the software (e.g. <code>"nginx"</code>).
   * @param string $version
   *   The version string of the software.
   * @return string
   *   The absolute path to the source files.
   */
  final protected function getSourcePath($name, $version) {
    global $kernel;
    return "{$kernel->configuration->directory->src}/{$name}-{$version}";
  }

  /**
   * Compare installed version against desired version.
   *
   * @param string $package
   *   The package's name.
   * @param string $version
   *   The package's version that should be installed.
   * @return boolean
   *   <code>TRUE</code> if the package isn't installed with the desired version (it's either not installed at all or
   *   an older version is installed). <code>FALSE</code> if the package is already installed with the desired version.
   */
  final protected function versionCompare($package, $version) {
    if ($this->force === false) {
      exec("dpkg --status {$package}", $output);
      if (!empty($output)) {
        $installed = preg_replace("/.*Version: ([a-z0-9\.-_]+).*/i", "$1", implode(" ", $output));
        if (version_compare($installed, $version) < 1) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Start a service.
   *
   * @param string $name
   *   The name of the service.
   * @param boolean $startOnBoot [optional]
   *   If this service should be started on boot, defaults to <code>TRUE</code>.
   * @return $thi
   * @throws \InvalidArgumentException
   */
  final protected function serviceStart($name, $startOnBoot = true) {
    // @devStart
    // @codeCoverageIngoreStart
    if (empty($name)) {
      throw new \InvalidArgumentException("Service \$name cannot be empty");
    }
    if (is_string($name) === false) {
      throw new \InvalidArgumentException("Service \$name must be of type string");
    }
    if (is_bool($startOnBoot) === false) {
      throw new \InvalidArgumentExcepiton("Service \$startOnBoot must be of type boolean");
    }
    $initScript = "/etc/init.d/{$name}";
    if (file_exists($initScript) === false) {
      throw new \LogicException("Couldn't find init script for {$name} in /etc/init.d");
    }
    $this->fsRealpath($initScript);
    if (is_executable($initScript) === false) {
      $this->fsChangeMode($initScript, 0755, "root", "root");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $action = $this->shellExecute("service {$name} status") === 0 ? "restart" : "start";
    $this->shellExecute("service {$name} {$action}");

    if ($startOnBoot !== false) {
      $args = is_string($startOnBoot) === true ? $startOnBoot : null;
      $this->shellExecute("update-rc.d {$name} defaults {$args}");
    }

    return $this;
  }

  /**
   * Validate the configuration value(s).
   *
   * @return this
   */
  public function validate() {
    // Defaults to doing nothing
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
  final protected function write($messages, $level = Output::VERBOSITY_VERBOSE, $newline = true, $type = Output::OUTPUT_NORMAL) {
    if ($this->output && $this->output->getVerbosity() >= $level) {
      $this->output->write($messages, $newline, $type);
    }
    return $this;
  }

}
