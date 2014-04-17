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
namespace MovLib\Console\Command\Install;

use \MovLib\Console\Command\Install\NginxRoutes;
use \MovLib\Data\FileSystem;
use \Symfony\Component\Console\Input\StringInput;
use \Symfony\Component\Console\Output\ConsoleOutput;
use \Symfony\Component\Console\Output\OutputInterface;

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
final class Nginx extends \MovLib\Console\Command\Install\AbstractInstallCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * URI to the directory that contains the HTTPS keys and certificates.
   *
   * @var string
   */
  const HTTPS_KEY_DIR_URI = "dr://etc/nginx/https/keys";


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("nginx");
    $this->setDescription("Install nginx (privileged)");
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
          $this->kernel->registerFileForDeletion($archive);
          $this->kernel->registerFileForDeletion($path);
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
   * Import HTTPS keys and certificates.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output [optional]
   *   Output instance for writing to console.
   * @return this
   * @throws \RuntimeException
   *   If no keys and certificates were found in the root's home directory.
   */
  public function importKeysAndCertificates(OutputInterface $output = null) {
    if ($output) {
      $this->output = $output;
    }

    if ($this->fs->privileged) {
      $this->writeVerbose("Importing HTTPS keys and certificates...");

      if (!is_dir(self::HTTPS_KEY_DIR_URI) || $this->fs->isDirectoryEmpty(self::HTTPS_KEY_DIR_URI)) {
        $this->writeVeryVerbose("Key and certificate directory is empty...");

        $rootKeys = "/root/keys";
        if (is_dir($rootKeys) && !$this->fs->isDirectoryEmpty($rootKeys)) {
          $this->writeVeryVerbose("Found key and certificate directory in root's home...");

          if (!is_dir(self::HTTPS_KEY_DIR_URI)) {
            $this->writeDebug("Creating directory <comment>" . self::HTTPS_KEY_DIR_URI . "</comment>");
            mkdir(self::HTTPS_KEY_DIR_URI, 0660);
            chown(self::HTTPS_KEY_DIR_URI, "root");
            chgrp(self::HTTPS_KEY_DIR_URI, "root");
          }
          $httpsKeysRealpath = $this->fs->realpath(self::HTTPS_KEY_DIR_URI);

          $this->writeDebug("Going through <comment>{$rootKeys}</comment>");
          /* @var $fileinfo \SplFileInfo */
          foreach ($this->fs->getRecursiveIterator($rootKeys, \RecursiveIteratorIterator::SELF_FIRST) as $fileinfo) {
            $source      = $fileinfo->getRealPath();
            $destination = str_replace($rootKeys, $httpsKeysRealpath, $source);
            if ($fileinfo->isDir()) {
              $this->writeDebug("Creating directory <comment>{$destination}</comment>");
              mkdir($destination, 0770);
              chown($destination, "root");
              chgrp($destination, "root");
            }
            else {
              $this->writeDebug("Copying <comment>{$source}</comment> to <comment>{$destination}</comment>");
              copy($source, $destination);
              chmod($destination, 0660);
              chown($destination, "root");
              chgrp($destination, "root");
            }
          }

          $this->writeVerbose("<info>Successfully copied all keys and certificates!");
        }
        else {
          throw new \RuntimeException("Couldn't find HTTPS keys and certificates in '{$rootKeys}'.");
        }
      }
    }

    return $this;
  }

  /**
   * Get the nginx configuration.
   *
   * @return \MovLib\Stub\Configuration\Nginx
   *   The global nginx configuration.
   * @throws \LogicException
   */
  protected function getConfiguration() {
    if (empty($this->kernel->configuration->nginx)) {
      throw new \LogicException("Missing {$this->getName()} configuration in global configuration file!");
    }
    return $this->kernel->configuration->nginx;
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

    $this->exec("make build", $sourceDirectories[$name]);

    if (file_exists($this->etcTarget)) {
      $this->writeDebug(
        "Deleting symbolic {$name} configuration link to ensure that files aren't overwritten during installation..."
      );
      FileSystem::delete($this->etcTarget);
    }

    $this->checkinstall($sourceDirectories[$name], $name, $conf->version, [
      "provides='{$name}'",
      "requires='build-essential,libc6'"
    ]);

    if (file_exists($this->etcTarget)) {
      $this->writeDebug("Deleting default {$name} configuration...");
      unlink($this->etcTarget);
    }

    $this->writeDebug("Linking {$name} configuration...");
    $this->fs->symlink($this->etcSource, $this->etcTarget);
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
    // We have to re-configure after patching.
    // @link https://rt.openssl.org/Ticket/Display.html?id=607&user=guest&pass=guest
    //$this->exec("./config reconf", $opensslPath);
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
