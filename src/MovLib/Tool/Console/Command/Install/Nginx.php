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
namespace MovLib\Tool\Console\Command\Install;

use \MovLib\Data\FileSystem;
use \MovLib\Tool\Console\Command\Production\NginxRoutes;
use \Symfony\Component\Console\Input\StringInput;
use \Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Install nginx.
 *
 * @todo Put website into maintenance mode before starting new nginx version.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Nginx extends AbstractInstallCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Canonical absolute path to the nginx target configuration directory.
   *
   * @var string
   */
  protected $etcTarget;

  /**
   * Canonical absolute path to the nginx source configuration directory.
   *
   * @see Nginx::configure()
   * @var string
   */
  protected $etcSource;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function configure() {
    global $kernel;
    $name = "nginx";
    $this->setName($name);
    $this->setDescription(
      "Install nginx according to the current global configuration. Any installed nginx version will be uninstalled " .
      "directly before the installation of the newly compiled nginx version."
    );
    $this->etcTarget = "/etc/{$name}";
    $this->etcSource = "{$kernel->documentRoot}{$this->etcTarget}";
  }

  /**
   * Download nginx modules and add them to the configure arguments.
   *
   * @param array $modules
   *   Array containing the modules to add.
   * @param array $configureArguments
   *   The arguments array of the configure options.
   * @return this
   */
  protected function downloadModules(array $modules, array &$configureArguments) {
    $name = $this->getName();
    $this->writeDebug("Downloading {$name} modules...", self::MESSAGE_TYPE_COMMENT);
    foreach ($modules as $url) {
      $basename = basename($url);
      $this->writeDebug("Adding module '{$basename}' to {$name}...");
      $configureArguments[] = "add-module='{$this->download($url)}'";
    }
    return $this;
  }

  /**
   * Download source files for compilation of nginx.
   *
   * @param array $paths
   *   Used to collect canonical absolute paths to the downloaded source files.
   * @param string $nginxVersion
   *   The nginx version string.
   * @param string $opensslVersion
   *   The OpenSSL version string.
   * @param string $pcreVersion
   *   The PCRE version string.
   * @return this
   */
  protected function downloadSources(array &$paths, $nginxVersion, $opensslVersion, $pcreVersion) {
    foreach ([
      "nginx"   => "http://nginx.org/download/nginx-{$nginxVersion}.tar.gz",
      "openssl" => "http://www.openssl.org/source/openssl-{$opensslVersion}.tar.gz",
      "pcre"    => "http://downloads.sourceforge.net/pcre/pcre-{$pcreVersion}.tar.gz",
      "zlib"    => "git://github.com/madler/zlib.git",
    ] as $name => $url) {
      $this->writeDebug("Downloading {$name} source files...", self::MESSAGE_TYPE_COMMENT);
      $path = $this->download($url);

      // @todo The PCRE archive won't extract because of a checksum mismatch. This is a naive workaround.
      if (($basename = basename($url, ".tar.gz")) == "pcre-{$pcreVersion}") {
        $archive   = $path;
        $directory = dirname($path);
        $path      = "{$directory}/{$basename}";
        $this->exec("tar --extract --gzip --file '{$archive}'", $directory);
        if ($this->input->getOption("keep") === false) {
          $this->registerFileForDeletion($archive);
          $this->registerFileForDeletion($path);
        }
      }
      elseif (pathinfo($url, PATHINFO_EXTENSION) != "git") {
        $path = $this->extract($path);
      }
      $paths[$name] = $path;
    }

    return $this;
  }

  /**
   * Get the nginx configuration.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return \MovLib\Stub\Configuration\Nginx
   *   The global nginx configuration.
   * @throws \LogicException
   */
  protected function getConfiguration() {
    global $kernel;
    if (empty($kernel->configuration->nginx)) {
      throw new \LogicException("Missing {$this->getName()} configuration in global configuration file!");
    }
    return $kernel->configuration->nginx;
  }

  /**
   * Install nginx.
   *
   * @param \MovLib\Stub\Configuration\Nginx $conf
   *   The global nginx configuration.
   * @return this
   */
  protected function install($conf) {
    $name = $this->getName();
    $sourceDirectories = [];
    $this->downloadSources($sourceDirectories, $conf->version, $conf->opensslVersion, $conf->pcreVersion);
    $this->patchOpenSSL($sourceDirectories["openssl"]);
    $this->expandPlaceholderTokens($conf->configure->arguments, $sourceDirectories);
    $this->downloadModules($conf->modules, $conf->configure->arguments);
    $this->configureInstallation($sourceDirectories[$name], $conf->configure);

    if (file_exists($this->etcTarget)) {
      $this->writeDebug(
        "Deleting symbolic {$name} configuration link to ensure that files aren't overwritten during installation..."
      );
      FileSystem::delete($this->etcTarget);
    }

    $this->exec("make build", $sourceDirectories[$name]);
    $this->checkinstall($sourceDirectories[$name], $name, $conf->version, [
      "provides='{$name}'",
      "requires='build-essential,libc6'"
    ]);

    if (file_exists($this->etcTarget)) {
      $this->writeDebug("Deleting default {$name} configuration...");
      FileSystem::delete($this->etcTarget, true);
    }

    $this->writeDebug("Linking {$name} configuration...");
    FileSystem::createSymbolicLink($this->etcSource, $this->etcTarget);
    $this->exec("make upgrade && make clean", $sourceDirectories[$name]);

    (new NginxRoutes())->run(new StringInput(""), new ConsoleOutput($this->output->getVerbosity()));

    return $this;
  }

  /**
   * Apply OpenSSL patches.
   *
   * @param string $opensslPath
   *   Canonical absolute path to the OpenSSL source files.
   * @return this
   */
  protected function patchOpenSSL($opensslPath) {
    $this->writeVerbose("Patching OpenSSL...", self::MESSAGE_TYPE_COMMENT);
    foreach ([ "openssl-1.0.1f-fix_parallel_build-1.patch", "openssl-1.0.1f-fix_pod_syntax-1.patch" ] as $patch) {
      $patch = $this->download("http://www.linuxfromscratch.org/patches/blfs/svn/{$patch}");
      if (is_file($patch) === false) {
        $this->exec("patch --forward --input='{$patch}' --strip=1 --verbose", $opensslPath);
      }
    }
    // We have to touch this file, otherwise OpenSSL complains about out of date makefiles.
    // @link https://rt.openssl.org/Ticket/Display.html?id=607&user=guest&pass=guest
    touch("{$opensslPath}/Makefile.ssl");
    return $this;
  }

  /**
   * Validate the global nginx configuration.
   *
   * @param \MovLib\Stub\Configuration\Nginx $conf
   *   The global nginx configuration to validate.
   * @return this
   * @throws \LogicException
   */
  protected function validate($conf) {
    $name = $this->getName();
    $this->checkPrivileges();

    foreach ([ "version", "opensslVersion", "pcreVersion" ] as $version) {
      if (empty($conf->{$version})) {
        throw new \LogicException("The {$name} '{$version}' cannot be empty");
      }
    }

    if (preg_match("/[0-9]+\.[0-9]+\.[0-9]+/", $conf->version) !== 1) {
      throw new \LogicException("The {$name} 'version' must be given in the format: <major>.<minor>.<patch>");
    }

    if (preg_match("/[0-9]+\.[0-9]+\.[0-9]+[a-z]+/", $conf->opensslVersion) !== 1) {
      throw new \LogicException("The {$name} 'opensslVersion' must be given in the format: <major>.<minor>.<patch><release>");
    }

    if (preg_match("/[0-9]+\.[0-9]+/", $conf->pcreVersion) !== 1) {
      throw new \LogicException("The {$name} 'pcreVersion' must be given in the format: <major>.<minor>");
    }

    if (empty($conf->configure)) {
      throw new \LogicException("The {$name} 'configure' options cannot be empty");
    }

    if (empty($conf->configure->arguments) || !is_array($conf->configure->arguments)) {
      throw new \LogicException("The {$name} configure 'arguments' cannot be empty and must be of type array");
    }

    if (file_exists($this->etcSource) === false) {
      throw new \LogicException("The {$name} source configuration files are missing from '{$this->etcSource}'");
    }

    return $this;
  }

}
